<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

namespace KSA\Register\Api\User;

use DateTimeImmutable;
use Keestash\Api\Response\JsonResponse;
use Keestash\Exception\User\State\UserStateException;
use KSA\Register\Event\UserRegistrationConfirmedEvent;
use KSP\Api\IResponse;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\Event\IEventService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class Confirm implements RequestHandlerInterface {

    public function __construct(
        private readonly IUserStateRepository $userStateRepository
        , private readonly IEventService      $eventService
        , private readonly LoggerInterface    $logger
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $body  = $request->getParsedBody();
        $token = $body['token'] ?? null;

        if (null === $token) {
            $this->logger->debug('no token given');
            return new JsonResponse([], IResponse::BAD_REQUEST);
        }

        $lockedUsers = $this->userStateRepository->getLockedUsers();
        $userState   = null;
        /** @var IUserState $us */
        foreach ($lockedUsers->toArray() as $us) {
            if ($us->getStateHash() === $token) {
                $userState = $us;
                break;
            }
        }

        if (null === $userState) {
            $this->logger->info('no userState found');
            return new JsonResponse([], IResponse::BAD_REQUEST);
        }

        if ($userState->getCreateTs() < ((new DateTimeImmutable())->modify('-7 day'))) {
            $this->logger->warning('userState expired', ['userState' => $userState]);
            return new JsonResponse([], IResponse::NOT_FOUND);
        }

        try {
            $this->userStateRepository->unlock(
                $userState->getUser()
            );
        } catch (UserStateException $exception) {
            $this->logger->error('error unlocking user', ['exception' => $exception]);
            return new JsonResponse([], IResponse::BAD_REQUEST);
        }

        $this->eventService->execute(
            new UserRegistrationConfirmedEvent(
                $userState->getUser()
                , 1
            )
        );

        return new JsonResponse(
            []
            , IResponse::OK
        );
    }

}