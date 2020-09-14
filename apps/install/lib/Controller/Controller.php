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
use Keestash\Legacy\Legacy;
use KSP\Core\Controller\FullScreen\FullscreenAppController;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\L10N\IL10N;

class Controller extends FullscreenAppController {

    public const TEMPLATE_INSTALL_APPS                 = "install.twig";
    public const INSTALL_TYPE_NOTHING_TO_UPDATE        = 0;
    public const INSTALL_TYPE_INSTALL_APPS             = 1;
    public const INSTALL_TYPE_UPDATE_APPS              = 2;
    public const INSTALL_TYPE_INSTALL_AND_UPGRADE_APPS = 3;
    public const INSTALL_TYPE_ERROR                    = 4;

    private $legacy = null;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $l10n
        , Legacy $legacy
    ) {
        parent::__construct(
            $templateManager
            , $l10n
        );

        $this->legacy = $legacy;
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

        // Step 1: we remove all apps that are disabled in our db
        $loadedApps = $diff->removeDisabledApps($loadedApps, $installedApps);

        // Step 2: we determine all apps that needs to be installed
        $appsToInstall = $diff->getNewlyAddedApps($loadedApps, $installedApps);

        // Step 3: we check if one of our loaded apps has a new version
        // at this point, we can be sure that both maps contain the same
        // apps
        $appsToUpgrade = $diff->getAppsThatNeedAUpgrade($loadedApps, $installedApps);

        $this->getTemplateManager()
            ->replace(
                Controller::TEMPLATE_INSTALL_APPS
                , [
                    "installationHeader"                    => $this->getL10N()->translate("Installing Apps")
                    , "installationDescription"             => $this->getL10N()->translate("Your {$this->legacy->getApplication()->get('name')} instance is not fully installed yet. Follow the instructions below to complete the installation.")
                    , "installInstructionInstallApps"       => $this->getL10N()->translate("The following apps are going to be installed. Please click on \"Install\" to finish the process.")
                    , "installInstructionNothingToUpdate"   => $this->getL10N()->translate("Your {$this->legacy->getApplication()->get('name')} instance is installed. Please click on \"End\" to start using {$this->legacy->getApplication()->get('name')}.")
                    , "installInstructionUpdateApps"        => $this->getL10N()->translate("The following apps are going to be updated. Please click on \"Install\" to finish the process.")
                    , "installInstructionInstallUpdateApps" => $this->getL10N()->translate("The following apps are going to be installed and updated. Please click on \"Install\" to finish the process.")
                    , "installationDescriptionError"        => $this->getL10N()->translate("It seems to be that the installer has an error and could not install or update your apps. Please consult your server admin or try installing {$this->legacy->getApplication()->get('name')} again.")
                    , "updateInstruction"                   => $this->getL10N()->translate("The following apps are going to be updated. Please click on \"Install\" to finish the process.")
                    , "endUpdate"                           => $this->getL10N()->translate("End")
                    , "installApps"                         => $this->getL10N()->translate("Install")
                    , "updateApps"                          => $this->getL10N()->translate("Update")
                    , "appsToInstall"                       => $this->hashTableToArray($appsToInstall)
                    , "appsToUpdate"                        => $this->hashTableToArray($appsToUpgrade)
                    , "installType"                         => $this->getInstallType($appsToInstall, $appsToUpgrade)
                ]
            );
        $this->setAppContent(
            $this->getTemplateManager()->render(Controller::TEMPLATE_INSTALL_APPS)
        );
    }

    private function hashTableToArray(HashTable $hashTable): array {
        $array = [];
        foreach ($hashTable->keySet() as $key) {
            $array[] = $hashTable->get($key);
        }
        return $array;
    }

    private function getInstallType(HashTable $appsToInstall, HashTable $appsToUpgrade): int {
        if (0 === $appsToInstall->size() && 0 === $appsToUpgrade->size()) return Controller::INSTALL_TYPE_NOTHING_TO_UPDATE;
        if ($appsToInstall->size() > 0 && 0 === $appsToUpgrade->size()) return Controller::INSTALL_TYPE_INSTALL_APPS;
        if (0 === $appsToInstall->size() && $appsToUpgrade->size() > 0) return Controller::INSTALL_TYPE_UPDATE_APPS;
        if ($appsToInstall->size() > 0 && $appsToUpgrade->size() > 0) return Controller::INSTALL_TYPE_INSTALL_AND_UPGRADE_APPS;
        return self::INSTALL_TYPE_ERROR;
    }

    public function afterCreate(): void {

    }

}
