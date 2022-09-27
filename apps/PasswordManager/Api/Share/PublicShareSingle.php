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

namespace KSA\PasswordManager\Api\Share;

use DateTime;
use Keestash\Api\Response\JsonResponse;
use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSA\PasswordManager\Event\PublicShare\PasswordViewed;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\PublicShareRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSP\Api\IResponse;
use KSP\Core\Manager\EventManager\IEventManager;
use KSP\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PublicShareSingle implements RequestHandlerInterface {

    private PublicShareRepository $shareRepository;
    private NodeRepository        $nodeRepository;
    private CredentialService     $credentialService;
    private IL10N                 $translator;
    private IEventManager         $eventManager;

    public function __construct(
        IL10N                   $l10n
        , PublicShareRepository $shareRepository
        , NodeRepository        $nodeRepository
        , CredentialService     $credentialService
        , IEventManager         $eventManager
    ) {
        $this->translator        = $l10n;
        $this->shareRepository   = $shareRepository;
        $this->nodeRepository    = $nodeRepository;
        $this->credentialService = $credentialService;
        $this->eventManager      = $eventManager;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $hash = $request->getAttribute('hash');

        if (null === $hash) {
            $this->eventManager->execute(
                new PasswordViewed(
                    array_merge(
                        $_SERVER
                        , ['passwordSeen' => false]
                    )
                    , new DateTime()
                )
            );
            return new JsonResponse(
                [
                    "message" => $this->translator->translate("no password found for hash")
                    , "hash"  => $hash
                ]
                , IResponse::BAD_REQUEST
            );
        }

        $share = $this->shareRepository->getShare($hash);

        if (null === $share || $share->isExpired()) {
            $this->eventManager->execute(
                new PasswordViewed(
                    array_merge(
                        $_SERVER
                        , [
                            'passwordSeen'   => false
                            , 'shareExists'  => null === $share
                            , 'shareExpired' => null !== $share ? $share->isExpired() : null
                        ]
                    )
                    , new DateTime()
                )
            );
            return new JsonResponse(
                [
                    "message" => $this->translator->translate("no share found")
                ]
                , IResponse::NOT_FOUND
            );
        }

        try {
            /** @var Credential $node */
            $node = $this->nodeRepository->getNode($share->getNodeId(), 0, 1);
        } catch (PasswordManagerException $exception) {
            return new JsonResponse(['no data found'], IResponse::NOT_FOUND);
        }

        $this->eventManager->execute(
            new PasswordViewed(
                array_merge(
                    $_SERVER
                    , ['passwordSeen' => true]
                )
                , new DateTime()
            )
        );

        return new JsonResponse(
            [
                "response_code" => IResponse::OK
                , "decrypted"   => $this->credentialService->getDecryptedPassword($node)
            ]
            , IResponse::OK
        );


    }

}
