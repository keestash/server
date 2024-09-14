<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
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

namespace KSA\PasswordManager\Api\Node\Share\Public;

use DateTimeImmutable;
use Exception;
use Keestash\Api\Response\JsonResponse;
use KSA\PasswordManager\Entity\IResponseCodes;
use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSA\PasswordManager\Entity\Share\NullShare;
use KSA\PasswordManager\Event\PublicShare\PasswordViewed;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\PublicShareRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Service\Node\Share\ShareService;
use KSP\Api\IResponse;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\HTTP\IResponseService;
use KSP\Core\Service\User\IUserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class PublicShareSingle implements RequestHandlerInterface {

    public function __construct(
        private PublicShareRepository $shareRepository
        , private NodeRepository      $nodeRepository
        , private CredentialService   $credentialService
        , private IEventService       $eventManager
        , private ShareService        $shareService
        , private LoggerInterface     $logger
        , private IResponseService    $responseService
        , private IUserService        $userService
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        try {
            $body      = (array) $request->getParsedBody();
            $hash      = (string) $request->getAttribute('hash');
            $password  = $body['password'] ?? '';
            $share     = $this->shareRepository->getShare($hash);
            $isExpired = $this->shareService->isExpired($share);

            if ($share instanceof NullShare || $isExpired) {
                $this->triggerEvent(false, !($share instanceof NullShare), $isExpired, false);
                return new JsonResponse(
                    [
                        'responseCode' => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_NODE_SHARE_PUBLIC_NO_SHARE_EXISTS)
                    ]
                    , IResponse::NOT_FOUND
                );
            }

            $verified = $this->userService->verifyPassword($password, $share->getPassword());

            $this->logger->error('log stuff', ['pass'=>$password, 'sha'=>$share->getPassword()]);
            if (false === $verified) {
                $this->triggerEvent(false, true, false, false);
                return new JsonResponse(
                    [
                        'responseCode' => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_NODE_SHARE_PUBLIC_INCORRECT_PASSWORD)
                    ]
                    , IResponse::NOT_FOUND
                );
            }

            /** @var Credential $node */
            $node = $this->nodeRepository->getNode($share->getNodeId(), 0, 1);
            $this->triggerEvent(true, true, false, true);
            return new JsonResponse(
                [
                    "decrypted" => [
                        'userName' => $this->credentialService->getDecryptedUsername($node),
                        'password' => $this->credentialService->getDecryptedPassword($node)
                    ]
                ]
                , IResponse::OK
            );
        } catch (Exception $exception) {
            $this->logger->error('error with public share single', ['e' => $exception]);
            return new JsonResponse(['no data found'], IResponse::NOT_FOUND);
        }
    }

    private function triggerEvent(
        bool $seen,
        bool $shareExists,
        bool $expired,
        bool $passwordCorrect
    ): void {
        $this->eventManager->execute(
            new PasswordViewed(
                array_merge(
                    $_SERVER
                    , [
                        'passwordSeen'    => $seen,
                        'shareExists'     => $shareExists,
                        'expired'         => $expired,
                        'passwordCorrect' => $passwordCorrect
                    ]
                )
                , new DateTimeImmutable()
            )
        );
    }

}
