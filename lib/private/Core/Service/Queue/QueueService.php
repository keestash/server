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

namespace Keestash\Core\Service\Queue;

use doganoo\DI\DateTime\IDateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash\Core\DTO\Queue\EmailMessage;
use Keestash\Core\DTO\Queue\EventMessage;
use Keestash\Core\DTO\Queue\Stamp;
use Keestash\Exception\KeestashException;
use KSP\Core\DTO\Queue\IMessage;
use KSP\Core\Repository\Queue\IQueueRepository;
use KSP\Core\Service\Queue\IQueueService;

class QueueService implements IQueueService {

    private IQueueRepository $queueRepository;
    private IDateTimeService $dateTimeService;

    public function __construct(
        IQueueRepository   $queueRepository
        , IDateTimeService $dateTimeService
    ) {
        $this->queueRepository = $queueRepository;
        $this->dateTimeService = $dateTimeService;
    }

    public function prepareQueue(bool $forceAll = false): ArrayList {

        $messageList = new ArrayList();
        if (true === $forceAll) {
            $messages = $this->queueRepository->getQueue();
        } else {
            $messages = $this->queueRepository->getSchedulableMessages();
        }

        /** @var array $messageArray */
        foreach ($messages as $messageArray) {

            $type = $messageArray['type'];
            if ($type === IMessage::TYPE_EMAIL) {
                $message = new EmailMessage();
                $message->setType(IMessage::TYPE_EMAIL);
            } else if ($type === IMessage::TYPE_EVENT) {
                $message = new EventMessage();
                $message->setType(IMessage::TYPE_EVENT);
            } else {
                throw new KeestashException();
            }

            $message->setId((string) $messageArray["id"]);
            $message->setCreateTs(
                $this->dateTimeService->fromFormat((string) $messageArray["create_ts"])
            );
            $message->setPriority((int) $messageArray["priority"]);
            $message->setAttempts((int) $messageArray["attempts"]);
            $message->setReservedTs(
                $this->dateTimeService->fromFormat((string) $messageArray["reserved_ts"])
            );
            $message->setPayload(
                (array) json_decode(
                    (string) $messageArray["payload"]
                    , true
                    , 512
                    , JSON_THROW_ON_ERROR
                )
            );

            $stamps       = (array) json_decode($messageArray['stamps'], true);
            $stampObjects = [];
            /**
             * @var int    $key
             * @var  array $stamp
             */
            foreach ($stamps as $key => $stamp) {
                $stampObject = new Stamp();
                $stampObject->setName($stamp['name']);
                $stampObject->setValue($stamp['value']);
                $stampObject->setCreateTs(
                    $this->dateTimeService->fromFormat((string) $stamp['create_ts']['date'])
                );
                $stampObjects[$key] = $stampObject;
            }
            $message->setStamps(
                HashTable::fromIterable($stampObjects)
            );

            $messageList->add($message);
        }
        return $messageList;
    }


}