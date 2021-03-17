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

use doganoo\Backgrounder\BackgroundJob\Job;
use doganoo\Backgrounder\BackgroundJob\JobList;
use doganoo\DI\DateTime\IDateTimeService;
use Keestash\Core\Repository\AbstractRepository;
use Keestash\Exception\KeestashException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\BackgroundJob\IJob;
use KSP\Core\Repository\Job\IJobRepository;

class JobRepository extends AbstractRepository implements IJobRepository {

    private ?JobList         $jobList = null;
    private IDateTimeService $dateTimeService;

    public function __construct(
        IBackend $backend
        , IDateTimeService $dateTimeService
    ) {
        parent::__construct($backend);
        $this->dateTimeService = $dateTimeService;
    }

    public function updateJobs(JobList $jobList): bool {
        $updated = false;

        /** @var Job $job */
        foreach ($jobList as $job) {
            $updated = $this->updateJob($job);
        }

        $this->jobList = null;
        return $updated;
    }

    public function updateJob(Job $job): bool {
        $queryBuilder = $this->getQueryBuilder();

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
        $rowCount     = $queryBuilder->execute();

        if (0 === $rowCount) {
            throw new KeestashException('no rows updated');
        }

        return true;
    }

    public function replaceJobs(JobList $jobList): bool {
        $inserted = true;

        /** @var Job $job */
        foreach ($jobList as $job) {
            $inserted = $this->replaceJob($job);
        }

        return $inserted;
    }

    public function replaceJob(Job $job): bool {
        if (true === $this->hasJob($job)) {
            return $this->updateJob($job);
        }
        return $this->insert($job);
    }

    private function hasJob(Job $job): bool {

        /** @var IJob $listJob */
        foreach ($this->getJobList() as $listJob) {
            if ($job->getName() === $listJob->getName()) return true;
        }

        return false;

    }

    public function getJobList(): JobList {
        $list = new JobList();

        $queryBuilder = $this->getQueryBuilder();
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
        $result         = $queryBuilder->execute();
        $backgroundJobs = $result->fetchAllAssociative();

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
                : json_decode($info, true);

            $job = new Job();
            $job->setId($id);
            $job->setName($name);
            $job->setInterval($interval);
            $job->setType($type);
            $job->setLastRun($this->dateTimeService->fromFormat($lastRun));
            $job->setInfo($info);
            $job->setCreateTs($this->dateTimeService->fromFormat($createTs));
            $list->add($job);
        }
        $this->jobList = $list;
        return $list;
    }

    private function insert(Job $job): bool {
        $queryBuilder = $this->getQueryBuilder();

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
            ->setParameter(2, $job->getLastRun())
            ->setParameter(3, $lastRun)
            ->setParameter(4, $info)
            ->setParameter(5, $this->dateTimeService->toYMDHIS($job->getCreateTs()))
            ->setParameter(6, $job->getInterval())
            ->execute();

        $lastInsertId = (int) $this->getLastInsertId();
        if (0 === $lastInsertId) return false;

        return true;

    }


}
