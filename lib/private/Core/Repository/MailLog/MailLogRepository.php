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

namespace Keestash\Core\Repository\MailLog;

use Doctrine\DBAL\Exception;
use doganoo\DI\DateTime\IDateTimeService;
use Keestash\Core\DTO\MailLog\MailLog;
use Keestash\Exception\KeestashException;
use Keestash\Exception\Repository\NoRowsFoundException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\MailLog\IMailLog;
use KSP\Core\Repository\MailLog\IMailLogRepository;
use Psr\Log\LoggerInterface;

class MailLogRepository implements IMailLogRepository {

    public function __construct(private readonly IBackend           $backend, private readonly IDateTimeService $dateTimeService, private readonly LoggerInterface  $logger)
    {
    }

    /**
     * @param IMailLog $mailLog
     * @return IMailLog
     * @throws KeestashException
     */
    #[\Override]
    public function insert(IMailLog $mailLog): IMailLog {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->insert("`mail_log`")
                ->values(
                    [
                        "`id`"          => '?'
                        , "`subject`"   => '?'
                        , "`create_ts`" => '?'
                    ]
                )
                ->setParameter(0, $mailLog->getId())
                ->setParameter(1, $mailLog->getSubject())
                ->setParameter(2, $this->dateTimeService->toYMDHIS($mailLog->getCreateTs()))
                ->executeStatement();
            return $mailLog;
        } catch (Exception $exception) {
            $this->logger->error('error mail repository', ['exception' => $exception]);
            throw new KeestashException();
        }
    }

    /**
     * @param string $subject
     * @return IMailLog
     * @throws KeestashException
     * @throws NoRowsFoundException
     */
    #[\Override]
    public function getLatestBySubject(string $subject): IMailLog {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->select(
                [
                    'ml.id'
                    , 'ml.subject'
                    , 'ml.create_ts'
                ]
            )
                ->from('mail_log', 'ml')
                ->where('ml.subject = ?')
                ->setParameter(0, $subject)
                ->orderBy('`create_ts`', 'desc')
                ->setMaxResults(1);

            $result   = $queryBuilder->executeQuery();
            $rowCount = $result->rowCount();

            if (0 === $rowCount) {
                throw new NoRowsFoundException();
            }

            $row     = $result->fetchAllNumeric()[0];
            $mailLog = new MailLog();
            $mailLog->setId(
                (string) $row[0]
            );
            $mailLog->setSubject(
                (string) $row[1]
            );
            $mailLog->setCreateTs(
                $this->dateTimeService->fromFormat((string) $row[2])
            );
            return $mailLog;
        } catch (Exception $exception) {
            $this->logger->error('error getting maillog', ['exception' => $exception]);
            throw new KeestashException();
        }
    }

}