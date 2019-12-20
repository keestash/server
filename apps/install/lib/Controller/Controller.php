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

namespace KSA\Install\Controller;

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash;
use Keestash\App\Config\Diff;
use Keestash\Core\Permission\PermissionFactory;
use Keestash\Core\Service\App\InstallerService;
use Keestash\Core\System\Installation\App\LockHandler;
use KSA\Install\Application\Application;
use KSP\Core\Controller\FullscreenAppController;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\L10N\IL10N;

class Controller extends FullscreenAppController {

    private $permissionManager = null;
    private $templateManager   = null;
    private $lockHandler       = null;
    private $installer         = null;

    public function __construct(
        ITemplateManager $templateManager
        , IPermissionRepository $permissionManager
        , IL10N $l10n
        , LockHandler $lockHandler
        , InstallerService $installer
    ) {
        parent::__construct(
            $templateManager
            , $l10n
        );

        $this->templateManager   = $templateManager;
        $this->permissionManager = $permissionManager;
        $this->lockHandler       = $lockHandler;
        $this->installer         = $installer;
    }

    private function install(HashTable $table): bool {

        if (0 === $table->size()) return true;

        $migrationRan = $this->installer->runMigrations();
        $installed    = $this->installer->installAll($table);

        return true === $migrationRan && true === $installed;
    }

    public function onCreate(...$params): void {
        parent::setPermission(
            PermissionFactory::getDefaultPermission()
        );
    }

    public function create(): void {
        // We only check loadedApps if the system is
        // installed
        $loadedApps    = Keestash::getServer()->getAppLoader()->getApps();
        $installedApps = Keestash::getServer()->getAppRepository()->getAllApps();

        $diff = new Diff();

        // Step 1: we disable all apps that are disabled in our db
        $diff->removeDisabledApps($loadedApps, $installedApps);

        $appsToInstall = $diff->getNewlyAddedApps($loadedApps, $installedApps);

        $installed = $this->install($appsToInstall);

        // Step 3: we check if one of our loaded apps has a new version
        // at this point, we can be sure that both maps contain the same
        // apps
        $appsToUpgrade = $diff->getAppsThatNeedAUpgrade($loadedApps, $installedApps);

        $updated = $this->install($appsToUpgrade);

        $this->lockHandler->unlock();

        $template = Application::TEMPLATE_SUCCESS;
        $message  = parent::getL10N()->translate("Updated All your Apps");
        if (false === $installed || false === $updated) {
            $template = Application::TEMPLATE_ERROR;
            $message  = parent::getL10N()->translate("There was an error updating your apps. Please try it again");
        }

        parent::getTemplateManager()->replace(
            $template
            , [
                "message" => $message
                , "href"  => Keestash::getServer()->getAppLoader()->getDefaultApp()->getBaseRoute()
                , "name"  => Keestash::getServer()->getAppLoader()->getDefaultApp()->getName()
            ]
        );
        parent::render($template);
    }


    public function afterCreate(): void {

    }

}