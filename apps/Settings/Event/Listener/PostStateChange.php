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

namespace KSA\Settings\Event\Listener;

use Keestash\Core\Service\User\Event\UserStateDeleteEvent;
use KSP\Core\DTO\Event\IEvent;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Service\Event\Listener\IListener;
use Psr\Log\LoggerInterface as ILogger;

class PostStateChange implements IListener {

    private ILogger $logger;

    public function __construct(ILogger $logger) {
        $this->logger = $logger;
    }

    private function handleDelete(): bool {
        $this->logger->info('implement me :(');
        return true;
    }

    private function handleLock(): bool {
        $this->logger->info('implement me :(');
        return true;
    }

    /**
     * @param UserStateDeleteEvent $event
     */
    public function execute(IEvent $event): void {

        switch ($event->getStateType()) {
            case IUserState::USER_STATE_LOCK:
                $this->handleLock();
                break;
            case IUserState::USER_STATE_DELETE:
                $this->handleDelete();
                break;
            default:
                $this->logger->warning("do not know what to do with {$event->getStateType()}");
        }
    }

}
