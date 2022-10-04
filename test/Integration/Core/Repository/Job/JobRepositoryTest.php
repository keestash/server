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

namespace KST\Integration\Core\Repository\Job;

use DateTimeImmutable;
use Keestash\Core\DTO\BackgroundJob\Job;
use Keestash\Core\DTO\BackgroundJob\JobList;
use KSP\Core\DTO\BackgroundJob\IJobList;
use KSP\Core\Repository\Job\IJobRepository;
use KST\TestCase;

class JobRepositoryTest extends TestCase {

    public function testGetReplaceJobs(): void {
        /** @var IJobRepository $jobRepository */
        $jobRepository = $this->getService(IJobRepository::class);
        $jobList       = new JobList();

        $job = new Job();
        $job->setCreateTs(new DateTimeImmutable());
        $job->setName(JobRepositoryTest::class);
        $job->setType(\doganoo\Backgrounder\BackgroundJob\Job::JOB_TYPE_REGULAR);
        $job->setInterval(1);
        $job->setLastRun((new DateTimeImmutable())->modify('-1 day'));
        $jobList->add($job);

        $job = new Job();
        $job->setCreateTs(new DateTimeImmutable());
        $job->setName(JobRepositoryTest::class . '1');
        $job->setType(\doganoo\Backgrounder\BackgroundJob\Job::JOB_TYPE_REGULAR);
        $job->setInterval(1);
        $job->setLastRun((new DateTimeImmutable())->modify('-1 day'));
        $jobList->add($job);

        $jobList = $jobRepository->replaceJobs($jobList);
        $this->assertTrue($jobList instanceof IJobList);
        $this->assertTrue(2 === $jobList->length());
        $jobRepository->removeAll();
    }

    public function testGetJobList(): void {
        /** @var IJobRepository $jobRepository */
        $jobRepository = $this->getService(IJobRepository::class);
        $jobList       = new JobList();

        $job = new Job();
        $job->setCreateTs(new DateTimeImmutable());
        $job->setName(JobRepositoryTest::class);
        $job->setType(\doganoo\Backgrounder\BackgroundJob\Job::JOB_TYPE_REGULAR);
        $job->setInterval(1);
        $job->setLastRun((new DateTimeImmutable())->modify('-1 day'));
        $jobList->add($job);

        $job = new Job();
        $job->setCreateTs(new DateTimeImmutable());
        $job->setName(JobRepositoryTest::class . '1');
        $job->setType(\doganoo\Backgrounder\BackgroundJob\Job::JOB_TYPE_REGULAR);
        $job->setInterval(1);
        $job->setLastRun((new DateTimeImmutable())->modify('-1 day'));
        $jobList->add($job);

        $jobRepository->replaceJobs($jobList);
        $jobList = $jobRepository->getJobList();
        $this->assertTrue($jobList instanceof IJobList);
        $this->assertTrue(2 === $jobList->length());
        $jobRepository->removeAll();
    }

}