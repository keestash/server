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

namespace KST\Unit\Core\Service\Queue;

use DateTimeImmutable;
use Keestash\Core\DTO\Queue\EventMessage;
use Keestash\Core\DTO\Queue\Stamp;
use KSP\Core\DTO\Queue\IMessage;
use KSP\Core\Repository\Queue\IQueueRepository;
use KSP\Core\Service\Queue\IQueueService;
use KST\Unit\TestCase;
use Ramsey\Uuid\Uuid;

class QueueServiceTest extends TestCase {

    private IQueueService $queueService;

    #[\Override]
    protected function setUp(): void {
        parent::setUp();
        $this->queueService = $this->getService(IQueueService::class);
    }

    public function testPrepareQueue(): void {
        /** @var IQueueRepository $queueRepository */
        $queueRepository = $this->getService(IQueueRepository::class);

        $message = new EventMessage();
        $message->setId((string) Uuid::uuid4());
        $message->setPayload(
            [
                'listener' => QueueServiceTest::class
                , 'event'  => [
                'serialized' => QueueServiceTest::class
                , 'name'     => static::class
            ]
            ]
        );
        $message->setReservedTs(new DateTimeImmutable());
        $message->setAttempts(0);
        $message->setPriority(1);
        $message->setCreateTs(new DateTimeImmutable());

        $stamp = new Stamp();
        $stamp->setCreateTs(new DateTimeImmutable());
        $stamp->setName(QueueServiceTest::class);
        $stamp->setValue((string) Uuid::uuid4());
        $message->addStamp($stamp);
        $queueRepository->insert($message);

        $all = $this->queueService->getQueue(true);
        $this->assertTrue($all->length() === 1);

        /** @var IMessage $retrievedMessage */
        $retrievedMessage = $all->get(0);

        $this->assertTrue($retrievedMessage->getId() === $message->getId());
        // TODO fixme $this->assertTrue($retrievedMessage->getPayload() == $message->getPayload());
        $this->assertTrue($retrievedMessage->getReservedTs()->getTimestamp() === $message->getReservedTs()->getTimestamp());
        $this->assertTrue($retrievedMessage->getCreateTs()->getTimestamp() === $message->getCreateTs()->getTimestamp());
        $this->assertTrue($retrievedMessage->getAttempts() === $message->getAttempts());
        $this->assertTrue($retrievedMessage->getPriority() === $message->getPriority());
        // TODO activate after adding stamps $this->assertTrue($retrievedMessage->getStamps()->size() === $message->getStamps()->size());
    }

}