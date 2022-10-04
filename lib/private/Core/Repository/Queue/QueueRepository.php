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
use Keestash\Exception\Queue\QueueException;
use Keestash\Exception\Queue\QueueNotCreatedException;
use Keestash\Exception\Queue\QueueNotDeletedException;
use Keestash\Exception\Queue\QueueNotUpdatedException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\Queue\IMessage;
use KSP\Core\Repository\Queue\IQueueRepository;
use KSP\Core\Service\Logger\ILogger;

class QueueRepository implements IQueueRepository {

    private IDateTimeService $dateTimeService;
    private IBackend         $backend;
    private ILogger          $logger;

    public function __construct(
        IBackend           $backend
        , IDateTimeService $dateTimeService
        , ILogger          $logger
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
            $queryBuilder     = $this->backend->getConnection()->createQueryBuilder();
            $thirtyMinutesAgo = new DateTime();
            $thirtyMinutesAgo = $thirtyMinutesAgo->modify('-30 minute');
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
                ->setParameter(1, $this->dateTimeService->toYMDHIS($thirtyMinutesAgo));

            $result = $queryBuilder->executeQuery();
            return $result->fetchAllAssociative();
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
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->delete(
                'queue'
            )
                ->where('id = ?')
                ->setParameter(0, $message->getId())
                ->executeStatement();
        } catch (Exception $exception) {
            $this->logger->error('message not deleted', ['exception' => $exception]);
            throw new QueueNotDeletedException();
        }
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
                ->setParameter(3, json_encode($message->getPayload()))
                ->setParameter(4, $this->dateTimeService->toYMDHIS($message->getReservedTs()))
                ->setParameter(5, $this->dateTimeService->toYMDHIS($message->getCreateTs()))
                ->setParameter(6, json_encode($message->getStamps()->toArray()))
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

}