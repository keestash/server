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

namespace KSA\Install\Api;

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash;
use Keestash\Api\AbstractApi;
use Keestash\App\Config\Diff;
use Keestash\Core\Permission\PermissionFactory;
use Keestash\Core\Service\App\InstallerService;
use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Core\System\Installation\App\LockHandler;
use KSP\Api\IResponse;
use KSP\Core\DTO\IToken;
use KSP\L10N\IL10N;

class InstallApps extends AbstractApi {

    /** @var InstallerService */
    private $installerService;

    /** @var LockHandler */
    private $lockHandler;

    /** @var HTTPService */
    private $httpService;

    public function __construct(
        IL10N $l10n
        , InstallerService $installer
        , LockHandler $lockHandler
        , HTTPService $httpService
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->installerService = $installer;
        $this->lockHandler      = $lockHandler;
        $this->httpService      = $httpService;
    }

    public function onCreate(array $parameters): void {
        $this->setPermission(
            PermissionFactory::getDefaultPermission()
        );
    }

    public function create(): void {
        // We only check loadedApps if the system is
        // installed
        $loadedApps    = Keestash::getServer()->getAppLoader()->getApps();
        $installedApps = Keestash::getServer()->getAppRepository()->getAllApps();

        $diff = new Diff();

        // Step 1: we remove all apps that are disabled in our db
        $loadedApps = $diff->removeDisabledApps($loadedApps, $installedApps);

        // Step 2: we determine all apps that needs to be installed
        $appsToInstall = $diff->getNewlyAddedApps($loadedApps, $installedApps);

        // Step 3: Install them!
        $installed = $this->install($appsToInstall);

        // Step 4: we check if one of our loaded apps has a new version
        // at this point, we can be sure that both maps contain the same
        // apps
        $appsToUpgrade = $diff->getAppsThatNeedAUpgrade($loadedApps, $installedApps);

        $updated = $this->install($appsToUpgrade);

        if (true === $installed && true === $updated) {
            $this->lockHandler->unlock();

            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_OK
                , [
                    "route_to" => $this->httpService->getLoginRoute()
                ]
            );
            return;
        }

        $this->createAndSetResponse(
            IResponse::RESPONSE_CODE_NOT_OK
            , [
                "message" => [
                    "installed" => $installed
                    , "updated" => $updated
                ]
            ]
        );
    }

    private function install(HashTable $table): bool {

        if (0 === $table->size()) return true;

        $migrationRan = $this->installerService->runMigrations();
        $installed    = $this->installerService->installAll($table);

        return true === $migrationRan && true === $installed;
    }

    public function afterCreate(): void {

    }

}
