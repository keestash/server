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

namespace Keestash\Core\Repository\Session;

use DateTime;
use doganoo\DI\DateTime\IDateTimeService;
use doganoo\DIP\DateTime\DateTimeService;
use Keestash\Core\Repository\AbstractRepository;
use Keestash\Exception\KeestashException;
use KSP\Core\Backend\IBackend;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Repository\Session\ISessionRepository;
use Throwable;

class SessionRepository extends AbstractRepository implements ISessionRepository {

    private IDateTimeService $dateTimeService;
    private ILogger          $logger;

    public function __construct(
        IBackend $backend
        , DateTimeService $dateTimeService
        , ILogger $logger
    ) {
        parent::__construct($backend);
        $this->dateTimeService = $dateTimeService;
        $this->logger          = $logger;
    }

    public function open(): bool {
        return true;
    }

    public function get(string $id): string {
        try {
            $queryBuilder = $this->getQueryBuilder();
            $queryBuilder = $queryBuilder->select(
                [
                    'data'
                ]
            )
                ->from('session')
                ->where('id = ?')
                ->setParameter(0, $id);

            $result           = $queryBuilder->execute();
            $sessionData      = $result->fetchAllNumeric();
            $sessionDataCount = count($sessionData);

            if (0 === $sessionDataCount) {
                $this->logger->debug("no session data found!!");
                $this->logger->debug(json_encode($sessionData));
                return "";
            }

            if ($sessionDataCount > 1) {
                throw new KeestashException("found more then one user for the given name");
            }

            return (string) $sessionData[0][0] ?? '';
        } catch (Throwable $exception) {
            $this->logger->error(
                json_encode(
                    [
                        "id"                => $exception->getCode()
                        , "message"         => $exception->getMessage()
                        , "file"            => $exception->getFile()
                        , "line"            => $exception->getLine()
                        , "trace"           => json_encode($exception->getTrace())
                        , "trace_as_string" => $exception->getTraceAsString()
                    ]
                )
            );
            return "";
        }
    }

    public function getAll(): array {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder = $queryBuilder->select(
            [
                'id'
                , 'data'
                , 'update_ts'
            ]
        )
            ->from('session');
        return $queryBuilder->execute()->fetchAllNumeric();
    }

    public function replace(string $id, string $data): bool {
        try {
            // notice that we can not use any doctrine
            // support here as this seems to be an
            // MySQL only thing: https://stackoverflow.com/a/4561615/1966490
            $updateTs = $this->dateTimeService->toYMDHIS(new DateTime());
            $sql      = "REPLACE INTO `session`(`id`, `data`,`update_ts`) VALUES ('" . $id . "', '" . $data . "', '" . $updateTs . "')";
            return $this->execute($sql);
        } catch (Throwable $exception) {
            $this->logger->error('error while replacing session. This can be a normal behaviour (for instance during installation). Please look into the messages in level debug for more information');
            $this->logger->debug(
                json_encode(
                    [
                        "id"                => $exception->getCode()
                        , "message"         => $exception->getMessage()
                        , "file"            => $exception->getFile()
                        , "line"            => $exception->getLine()
                        , "trace"           => json_encode($exception->getTrace())
                        , "trace_as_string" => $exception->getTraceAsString()
                    ]
                )
            );
        }
        return false;
    }

    public function deleteById(string $id): bool {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder->delete('session')
                ->where('id = ?')
                ->setParameter(0, $id)
                ->execute() !== 0;
    }

    public function deleteByLastUpdate(int $maxLifeTime): bool {
        $queryBuilder = $this->getQueryBuilder();
        return $queryBuilder->delete('session')
                ->where('update_ts = ?')
                ->setParameter(0, (new DateTime())->getTimestamp() - $maxLifeTime)
                ->execute() !== 0;
    }

    public function close(): bool {
        return true;
    }

}
