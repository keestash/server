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

namespace Keestash\App;

use DateTime;
use doganoo\Backgrounder\BackgroundJob\Job;
use doganoo\Backgrounder\BackgroundJob\JobList;
use KSP\App\IApp;

class AppFactory {

    public static function toConfigApp(IApp $app): \KSP\App\Config\IApp {
        $configApp = new \Keestash\App\Config\App();
        $configApp->setId($app->getId());
        $configApp->setEnabled(true);
        $configApp->setCreateTs(new DateTime());
        $configApp->setVersion($app->getVersion());
        $configApp->setBackgroundJobs(
            AppFactory::buildBackgroundJobs(
                $app->getBackgroundJobs()
            )
        );
        return $configApp;
    }

    private static function buildBackgroundJobs(array $backgroundJobs): JobList {
        $jobList = new JobList();
        foreach ($backgroundJobs as $jobName => $data) {
            $job = new Job();
            $job->setName($jobName);
            $job->setCreateTs(new DateTime());
            $job->setLastRun(null);
            $job->setInfo(null);
            $job->setInterval((int) $data["interval"]);
            $job->setType((string) $data["type"]);
            $jobList->add($job);
        }
        return $jobList;
    }

}
