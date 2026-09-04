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

namespace Keestash\Core\Repository\Payment;

use Doctrine\DBAL\Exception;
use doganoo\DI\DateTime\DateTimeServiceInterface;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Core\DTO\Payment\Log;
use Keestash\Exception\Payment\PaymentException;
use Keestash\Exception\Payment\PaymentNotCreatedException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\Payment\ILog;
use KSP\Core\DTO\Payment\Type;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\Payment\IPaymentLogRepository;
use Psr\Log\LoggerInterface;
use Throwable;

class DefaultPaymentLogRepository implements IPaymentLogRepository {

    private const string TABLE_NAME = 'payment_log';

    public function __construct(
        private readonly IBackend                 $backend,
        private readonly DateTimeServiceInterface $dateTimeService,
        private readonly LoggerInterface          $logger
    ) {
    }

    #[\Override]
    public function insert(ILog $log): void {
        try {
            $encoded = json_encode($log->getLog(), JSON_THROW_ON_ERROR);

            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->insert('`' . self::TABLE_NAME . '`')
                ->values(
                    [
                        '`key`'       => '?'
                        , '`log`'     => '?'
                        , '`create_ts`' => '?'
                    ]
                )
                ->setParameter(0, $log->getKey())
                ->setParameter(1, $encoded)
                ->setParameter(2, $this->dateTimeService->toYMDHIS($log->getCreateTs()))
                ->executeStatement();
        } catch (Throwable $exception) {
            $this->logger->error('error inserting payment log', ['exception' => $exception]);
            throw new PaymentNotCreatedException();
        }
    }

    /**
     * @throws Exception
     * @throws PaymentException
     */
    #[\Override]
    public function update(ILog $log): ILog {
        try {
            $encoded = json_encode($log->getLog(), JSON_THROW_ON_ERROR);

            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->update('`' . self::TABLE_NAME . '`')
                ->set('`log`', '?')
                ->where('`key` = ?')
                ->setParameter(0, $encoded)
                ->setParameter(1, $log->getKey())
                ->executeStatement();
            return $log;
        } catch (Throwable $exception) {
            $this->logger->error('error updating payment log', ['exception' => $exception]);
            throw new PaymentException();
        }
    }

    /**
     * @throws PaymentException
     */
    #[\Override]
    public function get(string $key): ILog {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $rows         = $queryBuilder
                ->select(['`key`', '`log`', '`create_ts`'])
                ->from(self::TABLE_NAME)
                ->where('`key` = ?')
                ->setParameter(0, $key)
                ->orderBy('id', 'DESC')
                ->setMaxResults(1)
                ->executeQuery()
                ->fetchAllAssociative();
        } catch (Throwable $exception) {
            $this->logger->error('error retrieving payment log', ['exception' => $exception]);
            throw new PaymentException();
        }

        if (0 === count($rows)) {
            throw new PaymentException(sprintf('no payment log for key %s', $key));
        }

        return $this->toLog($rows[0]);
    }

    /**
     * @throws PaymentException
     */
    #[\Override]
    public function getByType(Type $type): ILog {
        foreach ($this->getAll() as $log) {
            if (($log->getLog()['status'] ?? null) === $type->value) {
                return $log;
            }
        }
        throw new PaymentException(sprintf('no payment log for type %s', $type->value));
    }

    /**
     * @throws PaymentException
     */
    #[\Override]
    public function getAll(): ArrayList {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $rows         = $queryBuilder
                ->select(['`key`', '`log`', '`create_ts`'])
                ->from(self::TABLE_NAME)
                ->orderBy('id', 'DESC')
                ->executeQuery()
                ->fetchAllAssociative();
        } catch (Throwable $exception) {
            $this->logger->error('error retrieving payment logs', ['exception' => $exception]);
            throw new PaymentException();
        }

        $list = new ArrayList();
        foreach ($rows as $row) {
            $list->add($this->toLog($row));
        }
        return $list;
    }

    /**
     * @throws PaymentException
     */
    #[\Override]
    public function getByUser(IUser $user): ILog {
        foreach ($this->getAll() as $log) {
            $logUser = $log->getLog()['user'] ?? [];
            $userId  = is_array($logUser) ? ($logUser['id'] ?? null) : null;
            if ((string) $userId === (string) $user->getId()) {
                return $log;
            }
        }
        throw new PaymentException(sprintf('no payment log for user %s', $user->getId()));
    }

    /**
     * @param array<string, mixed> $row
     * @throws PaymentException
     */
    private function toLog(array $row): ILog {
        try {
            /** @var array<string, mixed> $decoded */
            $decoded = (array) json_decode((string) $row['log'], true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            $this->logger->error('error decoding payment log', ['exception' => $exception]);
            throw new PaymentException();
        }

        return new Log(
            key: (string) $row['key'],
            log: $decoded,
            createTs: $this->dateTimeService->fromString((string) $row['create_ts'])
        );
    }

}
