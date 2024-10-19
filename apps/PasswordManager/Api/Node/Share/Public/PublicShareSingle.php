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
use Keestash\Core\DTO\Encryption\Credential\Credential;
use Keestash\Core\Service\Encryption\Encryption\KeestashEncryptionService;
use KSA\PasswordManager\Entity\IResponseCodes;
use KSA\PasswordManager\Entity\Share\NullShare;
use KSA\PasswordManager\Event\PublicShare\PasswordViewed;
use KSA\PasswordManager\Repository\PublicShareRepository;
use KSA\PasswordManager\Service\Node\Share\ShareService;
use KSP\Api\IResponse;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\HTTP\IResponseService;
use KSP\Core\Service\User\IUserService;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class PublicShareSingle implements RequestHandlerInterface {

    public function __construct(
        private PublicShareRepository       $shareRepository
        , private IEventService             $eventManager
        , private ShareService              $shareService
        , private LoggerInterface           $logger
        , private IResponseService          $responseService
        , private IUserService              $userService
        , private KeestashEncryptionService $encryptionService
    ) {
    }

    #[Override]
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

            if (false === $verified) {
                $this->triggerEvent(false, true, false, false);
                return new JsonResponse(
                    [
                        'responseCode' => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_NODE_SHARE_PUBLIC_INCORRECT_PASSWORD)
                    ]
                    , IResponse::NOT_FOUND
                );
            }

            $this->triggerEvent(true, true, false, true);

            $decryptedSecret = base64_decode($share->getSecret());
            $c               = new Credential();
            $c->setSecret($password);
            $decrypted = $this->encryptionService->decrypt(
                $c,
                $decryptedSecret
            );

            $decrypted = json_decode($decrypted, true, JSON_THROW_ON_ERROR);
            return new JsonResponse(
                [
                    "decrypted" => [
                        'userName' => $decrypted['username'],
                        'password' => $decrypted['password']
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
