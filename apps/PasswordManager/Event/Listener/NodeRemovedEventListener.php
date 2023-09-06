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

namespace KSA\PasswordManager\Event\Listener;

use KSA\Activity\Event\ReferenceRemovedEvent;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Event\Node\NodeRemovedEvent;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSP\Core\DTO\Event\IEvent;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\Event\Listener\IListener;

class NodeRemovedEventListener implements IListener {

    public function __construct(
        private readonly IEventService $eventService
    ) {
    }

    /**
     * @param IEvent|NodeRemovedEvent $event
     * @return void
     * @throws PasswordManagerException
     */
    public function execute(IEvent|NodeRemovedEvent $event): void {
        if (false === ($event instanceof NodeRemovedEvent)) {
            throw new PasswordManagerException();
        }
        $this->eventService->execute(
            new ReferenceRemovedEvent(
                ConfigProvider::APP_ID
                , (string) $event->getNodeId()
            )
        );
    }

}