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

namespace Keestash\Core\Repository\Job;

use Doctrine\DBAL\Exception;
use doganoo\DI\DateTime\IDateTimeService;
use Keestash\Core\DTO\BackgroundJob\Job;
use Keestash\Core\DTO\BackgroundJob\JobList;
use Keestash\Exception\Job\JobNotCreatedException;
use Keestash\Exception\Job\JobNotDeletedException;
use Keestash\Exception\Job\JobNotUpdatedException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\BackgroundJob\IJob;
use KSP\Core\DTO\BackgroundJob\IJobList;
use KSP\Core\Repository\Job\IJobRepository;
use Psr\Log\LoggerInterface as ILogger;

class JobRepository implements IJobRepository {

    private IDateTimeService $dateTimeService;
    private IBackend         $backend;
    private ILogger          $logger;

    public function __construct(
        IBackend           $backend
        , IDateTimeService $dateTimeService
        , ILogger          $logger
    ) {
        $this->backend         = $backend;
        $this->dateTimeService = $dateTimeService;
        $this->logger          = $logger;
    }

    private function updateJob(IJob $job): IJob {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();

        $lastRun = null === $job->getLastRun()
            ? null
            : $this->dateTimeService->toYMDHIS($job->getLastRun());

        $info = null === $job->getInfo()
            ? null
            : json_encode($job->getInfo());

        $queryBuilder = $queryBuilder->update('background_job')
            ->set('name', '?')
            ->set('interval', '?')
            ->set('type', '?')
            ->set('last_run', '?')
            ->set('info', '?')
            ->where('id = ?')
            ->setParameter(0, $job->getId())
            ->setParameter(1, $job->getInterval())
            ->setParameter(2, $job->getType())
            ->setParameter(3, $lastRun)
            ->setParameter(4, $info);
        $rowCount     = $queryBuilder->executeStatement();

        if (0 === $rowCount) {
            throw new JobNotUpdatedException('no rows updated');
        }

        return $job;
    }

    public function replaceJobs(IJobList $jobList): IJobList {
        /** @var Job $job */
        foreach ($jobList as $job) {
            $this->replaceJob($job);
        }
        return $jobList;
    }

    private function replaceJob(IJob $job): IJob {
        if (true === $this->hasJob($job)) {
            return $this->updateJob($job);
        }
        return $this->insert($job);
    }

    private function hasJob(IJob $job): bool {

        /** @var IJob $listJob */
        foreach ($this->getJobList() as $listJob) {
            if ($job->getName() === $listJob->getName()) return true;
        }

        return false;

    }

    public function getJobList(): IJobList {
        $list = new JobList();

        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->select(
            [
                'b.id'
                , 'b.name'
                , 'b.interval'
                , 'b.type'
                , 'b.last_run'
                , 'b.info'
                , 'b.create_ts'
            ]
        )
            ->from('background_job', 'b');
        $result         = $queryBuilder->executeQuery();
        $backgroundJobs = $result->fetchAllNumeric();
        foreach ($backgroundJobs as $row) {

            $id       = (int) $row[0];
            $name     = $row[1];
            $interval = (int) $row[2];
            $type     = $row[3];
            $lastRun  = $row[4];
            $info     = $row[5];
            $createTs = $row[6];

            $info = null === $info
                ? null
                : json_decode((string)$info, true);

            $job = new Job();
            $job->setId($id);
            $job->setName((string)$name);
            $job->setInterval($interval);
            $job->setType((string)$type);
            $job->setLastRun($this->dateTimeService->fromFormat((string) $lastRun));
            $job->setInfo($info);
            $job->setCreateTs($this->dateTimeService->fromFormat((string) $createTs));
            $list->add($job);
        }
        return $list;
    }

    private function insert(IJob $job): IJob {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();

        $lastRun = $job->getLastRun();
        $lastRun = null === $lastRun
            ? null
            : $this->dateTimeService->toYMDHIS($lastRun);
        $info    = $job->getInfo();
        $info    = null === $info
            ? null
            : json_encode($info);


        $queryBuilder->insert("`background_job`")
            ->values(
                [
                    "`name`"        => '?'
                    , "`type`"      => '?'
                    , "`last_run`"  => '?'
                    , "`info`"      => '?'
                    , "`create_ts`" => '?'
                    , "`interval`"  => '?'
                ]
            )
            ->setParameter(0, $job->getName())
            ->setParameter(1, $job->getType())
            ->setParameter(2, $lastRun)
            ->setParameter(3, $info)
            ->setParameter(4, $this->dateTimeService->toYMDHIS($job->getCreateTs()))
            ->setParameter(5, $job->getInterval())
            ->executeStatement();

        $lastInsertId = (int) $this->backend->getConnection()->lastInsertId();
        if (0 === $lastInsertId) {
            throw new JobNotCreatedException();
        }

        $job->setId($lastInsertId);
        return $job;

    }

    public function removeAll(): void {
        /** @var IJob $job */
        foreach ($this->getJobList() as $job) {
            $this->remove($job);
        }
    }

    private function remove(IJob $job): IJob {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->delete('`background_job`')
                ->where('id = ?')
                ->setParameter(0, $job->getId())
                ->executeStatement();
        } catch (Exception $exception) {
            $this->logger->error('error while deleting', ['exception' => $exception]);
            throw new JobNotDeletedException();
        }
        return $job;
    }

}
