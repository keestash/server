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

namespace Keestash\Core\Service\App;

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use KSP\Core\DTO\App\Config\IApp;
use KSP\Core\Repository\AppRepository\IAppRepository;
use KSP\Core\Repository\Job\IJobRepository;
use KSP\Core\Service\App\IAppService;
use KSP\Core\Service\Phinx\IMigrator;

final readonly class InstallerService {

    public const int PHINX_MIGRATION_EVERYTHING_WENT_FINE = 0;

    public function __construct(
        private IMigrator      $migrator,
        private IAppRepository $appRepository,
        private IJobRepository $jobRepository,
        private IAppService    $appService
    ) {
    }

    public function runMigrations(): bool {
        return $this->migrator->runApps();
    }

    public function installAll(HashTable $apps): bool {
        $installed = true;

        foreach ($apps->keySet() as $key) {
            /** @var \KSP\Core\DTO\App\IApp $app */
            $app          = $apps->get($key);
            $configApp    = $this->appService->toConfigApp($app);
            $appInstalled = $this->install($configApp);
            $installed    = $installed && $appInstalled;
        }

        return $installed;
    }

    public function install(IApp $app): bool {
        $apps = $this->appRepository->replace($app);
        if (false === $apps) return false;
        $this->jobRepository->replaceJobs($app->getBackgroundJobs());
        return true;
    }

}
