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

namespace KSA\Users\Event\Listener;

use Keestash\Core\Service\User\Event\UserStateDeleteEvent;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Manager\EventManager\IListener;
use Symfony\Contracts\EventDispatcher\Event;

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
     * @param Event|UserStateDeleteEvent $event
     */
    public function execute(Event $event): void {

        if (false === $event->isDeleted()) {
            $this->logger->info("user is not removed. Not making any actions");
            return;
        }

        $success = false;
        switch ($event->getStateType()) {
            case IUserState::USER_STATE_LOCK:
                $success = $this->handleLock();
                break;
            case IUserState::USER_STATE_DELETE:
                $success = $this->handleDelete();
                break;
            default:
                $this->logger->warning("do not know what to do with {$event->getStateType()}");
        }
    }

}
