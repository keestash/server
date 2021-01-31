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

namespace Keestash\Core\Manager\EventManager;

use KSP\Core\Manager\EventManager\IEventManager;
use KSP\Core\Manager\EventManager\IListener;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\Event;

class EventManager implements IEventManager {

    private EventDispatcher $eventDispatcher;

    public function __construct() {
        $this->eventDispatcher = new EventDispatcher();
    }

    public function execute(Event $event): void {
        $this->eventDispatcher->dispatch($event, get_class($event));
    }

    public function registerListener(string $eventName, IListener $event): void {
        $this->eventDispatcher->addListener($eventName, [$event, 'execute']);
    }

}
