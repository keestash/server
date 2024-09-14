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

namespace Keestash\Core\Service\Event;

use DateTimeImmutable;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Core\DTO\Event\ReservedEvent;
use Keestash\Core\DTO\Queue\EventMessage;
use KSP\Core\DTO\Event\IEvent;
use KSP\Core\Repository\Queue\IQueueRepository;
use KSP\Core\Service\Encryption\IBase64Service;
use KSP\Core\Service\Event\IEventService;
use Laminas\Serializer\Adapter\PhpSerialize;
use Ramsey\Uuid\Uuid;

class EventService implements IEventService {

    private array $listeners;

    public function __construct(
        private readonly IQueueRepository $queueRepository
        , private readonly IBase64Service $base64Service
    ) {
    }

    #[\Override]
    public function execute(IEvent $event): void {
        $listeners  = $this->listeners[$event::class] ?? [];
        $serializer = new PhpSerialize();

        $messageList = new ArrayList();
        foreach ($listeners as $listener) {

            if (false === is_string($listener)) {
                continue;
            }

            $message = new EventMessage();
            $message->setId((string) Uuid::uuid4());
            $message->setPayload(
                $this->base64Service->encryptArrayRecursive(
                    [
                        'listener' => $listener
                        , 'event'  => [
                        'serialized' => $serializer->serialize($event)
                        , 'name'     => $event::class
                    ]
                    ]
                )
            );
            $message->setReservedTs(
                $event instanceof ReservedEvent
                    ? $event->getReservedTs()
                    : new DateTimeImmutable()
            );
            $message->setAttempts(0);
            $message->setPriority($event->getPriority());
            $message->setCreateTs(new DateTimeImmutable());
            $messageList->add($message);

        }
        $this->queueRepository->bulkInsert($messageList);

    }

    #[\Override]
    public function registerAll(array $events): void {
        foreach ($events as $event => $listeners) {
            foreach ($listeners as $listener) {
                $this->register($event, $listener);
            }
        }
    }

    #[\Override]
    public function register(string $event, string $listener): void {
        $this->listeners[$event][] = $listener;
    }

}
