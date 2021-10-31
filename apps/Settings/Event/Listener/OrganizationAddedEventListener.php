<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

use KSA\Settings\Event\Organization\UserChangedEvent;
use KSA\Settings\Exception\SettingsException;
use KSP\Core\Manager\EventManager\IListener;
use KSP\Core\Service\Encryption\Key\IKeyService;
use Symfony\Contracts\EventDispatcher\Event;

class OrganizationAddedEventListener implements IListener {

    private IKeyService $keyService;

    public function __construct(IKeyService $keyService) {
        $this->keyService = $keyService;
    }

    /**
     * @param Event $event
     * @throws SettingsException
     */
    public function execute(Event $event): void {

        if (false === $event instanceof UserChangedEvent) {
            throw new SettingsException();
        }

        $this->keyService->createAndStoreKey(
            $event->getOrganization()
        );
    }

}