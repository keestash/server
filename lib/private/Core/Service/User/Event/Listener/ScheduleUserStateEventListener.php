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

namespace Keestash\Core\Service\User\Event\Listener;

use DateTimeImmutable;
use Keestash\Core\DTO\User\UserState;
use Keestash\Core\Service\User\Event\ScheduleUserStateEvent;
use Keestash\Exception\KeestashException;
use KSP\Core\DTO\Event\IEvent;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\Event\Listener\IListener;
use KSP\Core\Service\User\IUserStateService;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final readonly class ScheduleUserStateEventListener implements IListener {

    public function __construct(
        private LoggerInterface        $logger
        , private IUserStateRepository $userStateRepository
        , private IUserStateService    $userStateService
    ) {
    }

    public function execute(IEvent $event): void {
        if (false === ($event instanceof ScheduleUserStateEvent)) {
            throw new KeestashException();
        }

        // security check - event should not be scheduled before reserved ts
        $now = new DateTimeImmutable();

        if ($now < $event->getReservedTs()) {
            $this->logger->error(
                'scheduled too early'
                , [
                    'now'          => $now
                    , 'reservedTs' => $event->getReservedTs()
                ]
            );
            throw new KeestashException();
        }

        switch ($event->getStateType()) {
            case IUserState::USER_STATE_DELETE:
                $this->userStateService->forceDelete($event->getUser());
                break;
            case IUserState::USER_STATE_LOCK:
                $this->userStateService->forceLock($event->getUser());
                break;
            case IUserState::USER_STATE_REQUEST_PW_CHANGE:
                $this->userStateService->setState(
                    new UserState(
                        0,
                        $event->getUser(),
                        IUserState::USER_STATE_REQUEST_PW_CHANGE,
                        new DateTimeImmutable(),
                        new DateTimeImmutable(),
                        Uuid::uuid4()->toString()
                    )
                );
                break;
            default:
                $this->logger->error('no handler for state type', ['event' => $event]);
                throw new KeestashException();
        }
    }

}
