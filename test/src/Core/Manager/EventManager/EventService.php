<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace KST\Service\Core\Manager\EventManager;

use KSP\Core\DTO\Event\IEvent;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\Event\Listener\IListener;
use Psr\Container\ContainerInterface;

class EventService implements IEventService {

    private ContainerInterface $container;

    private array $listeners = [];

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function execute(IEvent $event): void {
        $listeners = $this->listeners[get_class($event)] ?? [];

        foreach ($listeners as $listener) {

            if (false === is_string($listener)) {
                continue;
            }

            $listenerObject = $this->container->get($listener);
            if ($listenerObject instanceof IListener) {
                $listenerObject->execute($event);
            }
        }

    }

    public function registerAll(array $events): void {
        foreach ($events as $event => $listeners) {
            foreach ($listeners as $listener) {
                $this->register($event, $listener);
            }
        }
    }

    public function register(string $event, string $listener): void {
        $this->listeners[$event][] = $listener;
    }

}