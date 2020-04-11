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

// we want to keep the global namespace clean.
// Therefore, we call our framework within an
// anonymous function.
use Keestash\App\AppFactory;
use KSP\Core\Repository\Job\IJobRepository;

(function () {

    chdir(dirname(__DIR__));

    require_once __DIR__ . '/lib/Keestash.php';

    Keestash::init();

    Keestash::getServer()->getAppLoader()->loadApp('users');
    $users = Keestash::getServer()->getAppLoader()->getApps()->get('users');
    $app = AppFactory::toConfigApp($users);
    /** @var IJobRepository $jobRepository */
    $jobRepository = Keestash::getServer()->query(IJobRepository::class);

    var_dump(
        (new ReflectionClass($jobRepository))->getName()
    );
    $jobRepository->replaceJobs($app->getBackgroundJobs());
    return true;
})();
