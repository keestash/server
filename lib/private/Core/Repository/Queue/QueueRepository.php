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

namespace Keestash\Core\Repository\Queue;

use DateTime;
use Doctrine\DBAL\Exception;
use doganoo\DI\DateTime\IDateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use JsonException;
use Keestash\Core\Backend\SQLBackend\BulkInsert;
use Keestash\Exception\Queue\QueueException;
use Keestash\Exception\Queue\QueueNotCreatedException;
use Keestash\Exception\Queue\QueueNotDeletedException;
use Keestash\Exception\Queue\QueueNotUpdatedException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\Queue\IMessage;
use KSP\Core\Repository\Queue\IQueueRepository;
use Psr\Log\LoggerInterface;

class QueueRepository implements IQueueRepository {

    private IDateTimeService $dateTimeService;
    private IBackend         $backend;
    private LoggerInterface  $logger;

    public function __construct(
        IBackend           $backend
        , IDateTimeService $dateTimeService
        , LoggerInterface  $logger
    ) {
        $this->dateTimeService = $dateTimeService;
        $this->backend         = $backend;
        $this->logger          = $logger;
    }

    /**
     * @return array
     * @throws QueueException
     */
    public function getQueue(): array {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->select(
                [
                    'q.id'
                    , 'q.create_ts'
                    , 'q.priority'
                    , 'q.attempts'
                    , 'q.reserved_ts'
                    , 'q.payload'
                    , 'q.stamps'
                ]
            )
                ->from('queue', 'q');

            $result = $queryBuilder->executeQuery();
            return $result->fetchAllAssociative();
        } catch (Exception $exception) {
            $this->logger->error('error getting queue', ['exception' => $exception]);
            throw new QueueException();
        }
    }

    /**
     * @return array
     * @throws QueueException
     */
    public function getSchedulableMessages(): array {
        try {
            $queryBuilder   = $this->backend->getConnection()->createQueryBuilder();
            $fiveSecondsAgo = new DateTime();
            $fiveSecondsAgo = $fiveSecondsAgo->modify('-5 second');
            $queryBuilder->select(
                [
                    'q.id'
                    , 'q.create_ts'
                    , 'q.priority'
                    , 'q.attempts'
                    , 'q.reserved_ts'
                    , 'q.payload'
                    , 'q.stamps'
                ]
            )
                ->from('queue', 'q')
                ->where('q.attempts < ?')
                ->andWhere('q.reserved_ts < ?')
                ->setParameter(0, 3)
                ->setParameter(1, $this->dateTimeService->toYMDHIS($fiveSecondsAgo));

            $result = $queryBuilder->executeQuery();
            return $result->fetchAllAssociative();
        } catch (Exception $exception) {
            $this->logger->error('error getting schedulable queue', ['exception' => $exception]);
            throw new QueueException();
        }
    }

    public function getByUuid(string $uuid): array {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->select(
                [
                    'q.id'
                    , 'q.create_ts'
                    , 'q.priority'
                    , 'q.attempts'
                    , 'q.reserved_ts'
                    , 'q.payload'
                    , 'q.stamps'
                ]
            )
                ->from('queue', 'q')
                ->where('q.id = ?')
                ->setParameter(0, $uuid);

            $result = $queryBuilder->executeQuery();
            return $result->fetchAllAssociative()[0] ?? [];
        } catch (Exception $exception) {
            $this->logger->error('error getting schedulable queue', ['exception' => $exception]);
            throw new QueueException();
        }
    }

    /**
     * @param IMessage $message
     * @return void
     * @throws QueueNotDeletedException
     */
    public function delete(IMessage $message): void {
        $this->deleteByUuid($message->getId());
    }

    /**
     * @param string $uuid
     * @return void
     * @throws QueueNotDeletedException
     */
    public function deleteByUuid(string $uuid): void {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->delete(
                'queue'
            )
                ->where('id = ?')
                ->setParameter(0, $uuid)
                ->executeStatement();
        } catch (Exception $exception) {
            $this->logger->error('message not deleted', ['exception' => $exception]);
            throw new QueueNotDeletedException();
        }
    }

    /**
     * @param ArrayList $messageList
     * @return void
     * @throws Exception
     * @throws JsonException
     */
    public function bulkInsert(ArrayList $messageList): void {
        $list = [];

        foreach ($messageList as $message) {
            $list[] = [
                "`id`"            => $message->getId()
                , "`priority`"    => $message->getPriority()
                , "`attempts`"    => $message->getAttempts()
                , "`payload`"     => json_encode(
                    $message->getPayload()
                    , JSON_THROW_ON_ERROR
                )
                , "`reserved_ts`" => $this->dateTimeService->toYMDHIS($message->getReservedTs())
                , "`create_ts`"   => $this->dateTimeService->toYMDHIS($message->getCreateTs())
                , "`stamps`"      => json_encode(
                    $message->getStamps()->toArray()
                    , JSON_THROW_ON_ERROR
                )
            ];
        }
        $bulkInsert = new BulkInsert($this->backend->getConnection());
        $bulkInsert->insert('`queue`', $list);
    }

    public function connect(): void {
        $this->backend->connect();
    }

    public function disconnect(): void {
        $this->backend->disconnect();
    }

    /**
     * @param IMessage $message
     * @return IMessage
     * @throws QueueNotCreatedException
     */
    public function insert(IMessage $message): IMessage {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->insert("`queue`")
                ->values(
                    [
                        "`id`"            => '?'
                        , "`priority`"    => '?'
                        , "`attempts`"    => '?'
                        , "`payload`"     => '?'
                        , "`reserved_ts`" => '?'
                        , "`create_ts`"   => '?'
                        , "`stamps`"      => '?'
                    ]
                )
                ->setParameter(0, $message->getId())
                ->setParameter(1, $message->getPriority())
                ->setParameter(2, $message->getAttempts())
                ->setParameter(3,
                    json_encode(
                        $message->getPayload()
                        , JSON_THROW_ON_ERROR
                    )
                )
                ->setParameter(4, $this->dateTimeService->toYMDHIS($message->getReservedTs()))
                ->setParameter(5, $this->dateTimeService->toYMDHIS($message->getCreateTs()))
                ->setParameter(6,
                    json_encode(
                        $message->getStamps()->toArray()
                        , JSON_THROW_ON_ERROR
                    )
                )
                ->executeStatement();
            return $message;
        } catch (Exception $exception) {
            $this->logger->error('error inserting queue', ['exception' => $exception]);
            throw new QueueNotCreatedException();
        }
    }

    /**
     * @param IMessage $message
     * @return IMessage
     * @throws QueueNotUpdatedException
     */
    public function update(IMessage $message): IMessage {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder = $queryBuilder->update('`queue`')
                ->set('`priority`', '?')
                ->set('`attempts`', '?')
                ->set('`payload`', '?')
                ->set('`reserved_ts`', '?')
                ->set('`create_ts`', '?')
                ->set('`stamps`', '?')
                ->where('`id` = ?')
                ->setParameter(0, $message->getPriority())
                ->setParameter(1, $message->getAttempts())
                ->setParameter(2, json_encode($message->getPayload()))
                ->setParameter(3, $this->dateTimeService->toYMDHIS($message->getReservedTs()))
                ->setParameter(4, $this->dateTimeService->toYMDHIS($message->getCreateTs()))
                ->setParameter(5, json_encode($message->getStamps()->toArray()))
                ->setParameter(6, $message->getId());

            $rowCount = $queryBuilder->executeStatement();

            if (0 === $rowCount) {
                throw new QueueNotUpdatedException();
            }

            return $message;
        } catch (Exception $exception) {
            $this->logger->error('error updating queue', ['exception' => $exception]);
            throw new QueueNotUpdatedException();
        }
    }

    /**
     * @param string $uuid
     * @param int    $attempts
     * @return void
     * @throws QueueNotUpdatedException
     */
    public function updateAttempts(string $uuid, int $attempts): void {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder = $queryBuilder->update('`queue`')
                ->set('`attempts`', '?')
                ->where('`id` = ?')
                ->setParameter(0, $attempts)
                ->setParameter(1, $uuid);

            $rowCount = $queryBuilder->executeStatement();

            if (0 === $rowCount) {
                throw new QueueNotUpdatedException();
            }

        } catch (Exception $exception) {
            $this->logger->error(
                'error updating queue',
                [
                    'exception' => $exception
                    , 'uuid'    => $uuid
                    , 'attempt' => $attempts
                ]
            );
            throw new QueueNotUpdatedException();
        }
    }

}