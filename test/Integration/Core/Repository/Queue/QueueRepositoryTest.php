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

namespace KST\Integration\Core\Repository\Queue;

use DateTimeImmutable;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash\Core\DTO\Queue\EventMessage;
use KSP\Core\DTO\Queue\IMessage;
use KSP\Core\Repository\Queue\IQueueRepository;
use KST\Integration\TestCase;
use Ramsey\Uuid\Uuid;

class QueueRepositoryTest extends TestCase {

    public function testAddAndUpdateAndDelete(): void {
        /** @var IQueueRepository $queueRepository */
        $queueRepository = $this->getService(IQueueRepository::class);

        $message = new EventMessage();
        $message->setId((string) Uuid::uuid4());
        $message->setCreateTs(new DateTimeImmutable());
        $message->setPriority(1);
        $message->setAttempts(0);
        $message->setReservedTs(new DateTimeImmutable());
        $message->setPayload([]);
        $message->setStamps(new HashTable());
        $message = $queueRepository->insert($message);
        $this->assertTrue($message instanceof IMessage);

        $message->setPayload(['updated' => true]);
        $message = $queueRepository->update($message);

        $this->assertTrue($message instanceof IMessage);
        $this->assertTrue(true === $message->getPayload()['updated']);

        $queueRepository->delete($message);
    }

    public function testGetAll(): void {
        /** @var IQueueRepository $queueRepository */
        $queueRepository = $this->getService(IQueueRepository::class);

        $message1 = new EventMessage();
        $message1->setId((string) Uuid::uuid4());
        $message1->setCreateTs(new DateTimeImmutable());
        $message1->setPriority(1);
        $message1->setAttempts(0);
        $message1->setReservedTs(new DateTimeImmutable());
        $message1->setPayload([]);
        $message1->setStamps(new HashTable());
        $message1 = $queueRepository->insert($message1);
        $this->assertTrue($message1 instanceof IMessage);

        $message2 = new EventMessage();
        $message2->setId((string) Uuid::uuid4());
        $message2->setCreateTs(new DateTimeImmutable());
        $message2->setPriority(1);
        $message2->setAttempts(0);
        $message2->setReservedTs(new DateTimeImmutable());
        $message2->setPayload([]);
        $message2->setStamps(new HashTable());
        $message2 = $queueRepository->insert($message2);
        $this->assertTrue($message2 instanceof IMessage);

        $message3 = new EventMessage();
        $message3->setId((string) Uuid::uuid4());
        $message3->setCreateTs(new DateTimeImmutable());
        $message3->setPriority(1);
        $message3->setAttempts(0);
        $message3->setReservedTs(new DateTimeImmutable());
        $message3->setPayload([]);
        $message3->setStamps(new HashTable());
        $message3 = $queueRepository->insert($message3);
        $this->assertTrue($message3 instanceof IMessage);

        $all = $queueRepository->getQueue();
        $this->assertIsArray($all);
        $this->assertCount(3, $all);

        $queueRepository->delete($message1);
        $queueRepository->delete($message2);
        $queueRepository->delete($message3);
    }
    public function testGetSchedulable(): void {
        /** @var IQueueRepository $queueRepository */
        $queueRepository = $this->getService(IQueueRepository::class);

        $message1 = new EventMessage();
        $message1->setId((string) Uuid::uuid4());
        $message1->setCreateTs(new DateTimeImmutable());
        $message1->setPriority(1);
        $message1->setAttempts(0);
        $message1->setReservedTs(new DateTimeImmutable());
        $message1->setPayload([]);
        $message1->setStamps(new HashTable());
        $message1 = $queueRepository->insert($message1);
        $this->assertTrue($message1 instanceof IMessage);

        $message2 = new EventMessage();
        $message2->setId((string) Uuid::uuid4());
        $message2->setCreateTs(new DateTimeImmutable());
        $message2->setPriority(1);
        $message2->setAttempts(0);
        $message2->setReservedTs(new DateTimeImmutable());
        $message2->setPayload([]);
        $message2->setStamps(new HashTable());
        $message2 = $queueRepository->insert($message2);
        $this->assertTrue($message2 instanceof IMessage);

        $message3 = new EventMessage();
        $message3->setId((string) Uuid::uuid4());
        $message3->setCreateTs(new DateTimeImmutable());
        $message3->setPriority(1);
        $message3->setAttempts(0);
        $message3->setReservedTs((new DateTimeImmutable())->modify('-31 minute'));
        $message3->setPayload([]);
        $message3->setStamps(new HashTable());
        $message3 = $queueRepository->insert($message3);
        $this->assertTrue($message3 instanceof IMessage);

        $all = $queueRepository->getSchedulableMessages();
        $this->assertIsArray($all);
        $this->assertCount(1, $all);

        $queueRepository->delete($message1);
        $queueRepository->delete($message2);
        $queueRepository->delete($message3);
    }

}