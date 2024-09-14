<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace KSA\PasswordManager\Api\Node\Pwned;

use doganoo\PHPAlgorithms\Datastructure\Set\HashSet;
use Keestash\Api\Response\JsonResponse;
use Keestash\Api\Response\NotFoundResponse;
use KSA\PasswordManager\Entity\Node\Pwned\Breaches;
use KSA\PasswordManager\Entity\Node\Pwned\Passwords;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\Node\PwnedBreachesRepository;
use KSA\PasswordManager\Repository\Node\PwnedPasswordsRepository;
use KSA\PasswordManager\Service\AccessService;
use KSA\Settings\Exception\SettingNotFoundException;
use KSA\Settings\Repository\IUserSettingRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class ChartData implements RequestHandlerInterface {

    public function __construct(
        private readonly PwnedBreachesRepository    $pwnedBreachesRepository
        , private readonly PwnedPasswordsRepository $pwnedPasswordsRepository
        , private readonly NodeRepository           $nodeRepository
        , private readonly LoggerInterface          $logger
        , private readonly AccessService            $accessService
        , private readonly IUserSettingRepository   $userSettingRepository
    ) {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface {
        /** @var IToken $token */
        $token           = $request->getAttribute(IToken::class);
        $user            = $token->getUser();
        $root            = null;
        $minimumSeverity = null;
        $lowSeverity     = 0;
        $passwordsData   = [];
        $breachesData    = [];

        try {
            $root            = $this->nodeRepository->getRootForUser($token->getUser());
            $minimumSeverity = $this->userSettingRepository->get('pwned.passwords.minimumSeverity', $user);
        } catch (PasswordManagerException $e) {
            $this->logger->info('no root for user found', ['exception' => $e, 'userId' => $user->getId()]);
            return new NotFoundResponse();
        } catch (SettingNotFoundException $e) {
            $this->logger->warning(
                'no minimumSeverity found for user'
                ,
                [
                    'exception'      => $e
                    , 'userId'       => $user->getId()
                    , 'defaultValue' => $minimumSeverity
                ]
            );
        }

        $passwords = $this->pwnedPasswordsRepository->getPwnedByNode($root, (int) $minimumSeverity?->getValue());

        /** @var Passwords $password */
        foreach ($passwords->toArray() as $key => $password) {
            if (
                false === $this->accessService->hasAccess($password->getNode(), $token->getUser())
                || $password->getSeverity() < $minimumSeverity
            ) {
                $passwords->remove($key);
                $lowSeverity++;
            }
            $passwordsData[] = [
                'node'       => [
                    'id'     => $password->getNode()->getId()
                    , 'name' => $password->getNode()->getName()
                ]
                , 'severity' => $password->getSeverity()
            ];
        }
        $breaches = $this->pwnedBreachesRepository->getPwnedByNode($root);
        /** @var Breaches $breach */
        foreach ($breaches->toArray() as $key => $breach) {

            if (
                false === $this->accessService->hasAccess($breach->getNode(), $token->getUser())
                || null === $breach->getHibpData()
            ) {
                continue;
            }

            $breachesData = [
                'node'   => [
                    'id'     => $breach->getNode()->getId()
                    , 'name' => $breach->getNode()->getName()
                ]
                , 'hibp' => $this->getHibpData($breach->getHibpData())
            ];
        }

        $breachesCount  = count($breachesData);
        $passwordsCount = count($passwordsData);
        $totalAmount    = $breachesCount + $passwordsCount;
        return new JsonResponse(
            [
                'passwords'   => [
                    'amount'        => $passwordsCount
                    , 'length'      => $passwordsCount
                    , 'lowSeverity' => $lowSeverity
                    , 'data'        => $passwordsData
                ]
                , 'breaches'  => [
                'amount'   => $breachesCount
                , 'length' => $breachesCount
                , 'data'   => $breachesData
            ],
                'totalAmount' => $totalAmount
            ]
            , IResponse::OK
        );

    }

    private function getHibpData(array $data): array {
        $dataClassesSet = new HashSet();
        $platforms      = new HashSet();
        foreach ($data as $d) {
            $dataClassesSet->addAll($d['DataClasses']);
            $platforms->add($d['Title']);
        }

        return [
            'types'       => $dataClassesSet->toArray()
            , 'platforms' => $platforms->toArray()
        ];
    }

}