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

namespace KSA\Install\Command;

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash\Command\KeestashCommand;
use Keestash\Core\Service\App\InstallerService;
use Keestash\Core\System\Installation\App\LockHandler;
use KSP\Core\Repository\AppRepository\IAppRepository;
use KSP\Core\Service\App\IAppService;
use KSP\Core\Service\App\ILoaderService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Install extends KeestashCommand {

    private InstallerService $installerService;
    private LockHandler      $lockHandler;
    private LoggerInterface  $logger;
    private ILoaderService   $loader;
    private IAppRepository   $appRepository;
    private IAppService      $appService;

    public function __construct(
        InstallerService  $installer
        , LockHandler     $lockHandler
        , LoggerInterface $logger
        , ILoaderService  $loader
        , IAppRepository  $appRepository
        , IAppService     $appService
    ) {
        parent::__construct();
        $this->installerService = $installer;
        $this->lockHandler      = $lockHandler;
        $this->logger           = $logger;
        $this->loader           = $loader;
        $this->appRepository    = $appRepository;
        $this->appService       = $appService;
    }

    protected function configure(): void {
        $this->setName("apps:install")
            ->setDescription("installs all apps");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        // We only check loadedApps if the system is
        // installed
        $loadedApps    = $this->loader->getApps();
        $installedApps = $this->appRepository->getAllApps();

        // Step 1: we remove all apps that are disabled in our db
        $loadedApps = $this->appService->removeDisabledApps($loadedApps, $installedApps);

        // Step 2: we determine all apps that needs to be installed
        $appsToInstall = $this->appService->getNewlyAddedApps($loadedApps, $installedApps);

        // Step 3: Install them!
        $installed = $this->install($appsToInstall);

        // Step 4: we check if one of our loaded apps has a new version
        // at this point, we can be sure that both maps contain the same
        // apps
        $appsToUpgrade = $this->appService->getAppsThatNeedAUpgrade($loadedApps, $installedApps);

        $updated = $this->install($appsToUpgrade);

        if (true === $installed && true === $updated) {
            $this->lockHandler->unlock();
            return 0;
        }

        $this->writeError(
            (string) json_encode(
                [
                    "message" => [
                        "installed" => $installed
                        , "updated" => $updated
                    ]
                ]
                , JSON_PRETTY_PRINT
            )
            , $output
        );
        return 1;
    }

    private function install(HashTable $table): bool {

        $this->logger->debug('going to update ' . $table->size() . ' apps');
        if (0 === $table->size()) return true;

        $migrationRan = $this->installerService->runMigrations();
        $this->logger->debug('migration ran: ' . $migrationRan);
        $installed = $this->installerService->installAll($table);
        $this->logger->debug('installed: ' . $installed);

        return true === $migrationRan && true === $installed;
    }

}