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
use JsonException;
use Keestash\Core\DTO\Queue\EventMessage;
use Keestash\Core\DTO\Queue\Message;
use Keestash\Exception\Queue\QueueException;
use Keestash\Exception\Repository\NoRowsFoundException;
use KSP\Core\Repository\Queue\IQueueRepository;
use KSP\Core\Service\Encryption\IBase64Service;
use KSP\Core\Service\Queue\IQueueService;
use Psr\Log\LoggerInterface;

class QueueService implements IQueueService {

    public function __construct(private readonly IQueueRepository $queueRepository, private readonly IDateTimeService $dateTimeService, private readonly IBase64Service $base64Service, private readonly LoggerInterface $logger) {
    }

    #[\Override]
    public function getQueue(bool $forceAll = false): ArrayList {

        $messageList = new ArrayList();
        if (true === $forceAll) {
            $messages = $this->queueRepository->getQueue();
        } else {
            $messages = $this->queueRepository->getSchedulableMessages();
        }

        /** @var array $messageArray */
        foreach ($messages as $messageArray) {
            try {
                $message = new EventMessage();
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
                    $this->base64Service->decryptArrayRecursive(
                        (array) json_decode(
                            (string) $messageArray["payload"]
                            , true
                            , 512
                            , JSON_THROW_ON_ERROR
                        )
                    )
                );

                $messageList->add($message);
            } catch (JsonException $exception) {
                $this->logger->error(
                    'error parsing payload or stamps'
                    , [
                        'exception' => $exception
                        , 'message' => $messageArray
                    ]
                );
            }
        }
        return $messageList;
    }

    /**
     * @param string $uuid
     * @return Message
     * @throws JsonException
     * @throws NoRowsFoundException
     */
    #[\Override]
    public function getByUuid(string $uuid): Message {
        try {
            $messageArray = $this->queueRepository->getByUuid($uuid);

            $message = new EventMessage();
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
                $this->base64Service->decryptArrayRecursive(
                    (array) json_decode(
                        (string) $messageArray["payload"]
                        , true
                        , 512
                        , JSON_THROW_ON_ERROR
                    )
                )
            );

            return $message;
        } catch (JsonException $exception) {
            $this->logger->error(
                'error parsing payload or stamps'
                , [
                    'exception' => $exception
                    , 'message' => $messageArray ?? []
                ]
            );
            throw $exception;
        } catch (QueueException|NoRowsFoundException $e) {
            $this->logger->debug('no message found', ['exception' => $e]);
            throw new NoRowsFoundException();
        }
    }

    #[\Override]
    public function remove(string $uuid): void {
        $this->queueRepository->deleteByUuid($uuid);
    }

    #[\Override]
    public function updateAttempts(string $uuid, int $attempts): void {
        $this->queueRepository->updateAttempts($uuid, $attempts);
    }

}
