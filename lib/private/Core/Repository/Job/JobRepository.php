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

use doganoo\Backgrounder\BackgroundJob\Job as BackgrounderJob;
use doganoo\Backgrounder\BackgroundJob\JobList as BackgrounderJobList;
use doganoo\PHPUtil\Util\DateTimeUtil;
use Keestash\Core\DTO\BackgroundJob\Job;
use Keestash\Core\DTO\BackgroundJob\JobList;
use Keestash\Core\Repository\AbstractRepository;
use KSP\Core\DTO\BackgroundJob\IJob;
use KSP\Core\DTO\BackgroundJob\IJobList;
use KSP\Core\Repository\Job\IJobRepository;
use PDO;

class JobRepository extends AbstractRepository implements IJobRepository {

    public function getJobList(): IJobList {
        $list      = new JobList();
        $sql       = "SELECT
                    b.`id`
                    , b.`name`
                    , b.`interval`
                    , b.`type`
                    , b.`last_run`
                    , b.`info`
                    , b.`create_ts`
                FROM background_job b;";
        $statement = $this->prepareStatement($sql);
        if (null === $statement) return $list;
        $statement->execute();
        $list = new JobList();
        while ($row = $statement->fetch(PDO::FETCH_BOTH)) {

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
            $job->setLastRun(
                DateTimeUtil::fromMysqlDateTime($lastRun)
            );
            $job->setInfo($info);
            $job->setCreateTs(
                DateTimeUtil::fromMysqlDateTime($createTs)
            );
            $list->add($job);
        }
        return $list;
    }

    public function updateJobs(BackgrounderJobList $jobList): bool {
        $updated = false;

        /** @var Job $job */
        foreach ($jobList as $job) {
            $updated = $this->updateJob($job);
        }

        return $updated;
    }

    public function updateJob(BackgrounderJob $job): bool {
        $sql = "
                update `background_job`
                    set `name`      = :name
                      , `interval`  = :interval
                      , `type`      = :type
                      , `last_run`  = :last_run
                      , `info`      = :info
                    where `id` = :id;
        ";

        $statement = parent::prepareStatement($sql);

        if (null === $statement) {
            return false;
        }

        $name     = $job->getName();
        $interval = $job->getInterval();
        $type     = $job->getType();
        $lastRun  = $job->getLastRun();
        $info     = $job->getInfo();

        $lastRun = null === $lastRun
            ? null
            : DateTimeUtil::formatMysqlDateTime($lastRun);

        $info = null === $info
            ? null
            : json_encode($info);

        $statement->bindParam(":name", $name);
        $statement->bindParam(":interval", $interval);
        $statement->bindParam(":type", $type);
        $statement->bindParam(":last_run", $lastRun);
        $statement->bindParam(":info", $info);

        $statement->execute();

        return
            false === $this->hasErrors($statement->errorCode())
            && $statement->rowCount() > 0;

    }

}
