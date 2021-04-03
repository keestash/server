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

namespace Keestash\Core\Service\Event;

use KSP\Core\Manager\EventManager\IEventManager;
use KSP\Core\Service\Event\IEventDispatcher;
use Psr\Container\ContainerInterface;

class EventDispatcher implements IEventDispatcher {

    private IEventManager      $eventManager;
    private ContainerInterface $container;

    public function __construct(
        IEventManager $eventManager
        , ContainerInterface $container
    ) {
        $this->eventManager = $eventManager;
        $this->container    = $container;
    }

    public function register(array $events): void {

        foreach ($events as $event => $listener) {
            $this->eventManager->registerListener(
                $event
                , $this->container->get($listener)
            );

        }
    }

}