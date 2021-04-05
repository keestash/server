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
use Keestash\App\Config\Diff;
use Keestash\Legacy\Legacy;
use KSP\App\ILoader;
use KSP\Core\Controller\FullScreen\FullscreenAppController;
use KSP\Core\Repository\AppRepository\IAppRepository;
use KSP\Core\Service\Controller\IAppRenderer;
use KSP\L10N\IL10N;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class Controller extends FullscreenAppController {

    public const INSTALL_TYPE_NOTHING_TO_UPDATE        = 0;
    public const INSTALL_TYPE_INSTALL_APPS             = 1;
    public const INSTALL_TYPE_UPDATE_APPS              = 2;
    public const INSTALL_TYPE_INSTALL_AND_UPGRADE_APPS = 3;
    public const INSTALL_TYPE_ERROR                    = 4;

    private Legacy                    $legacy;
    private ILoader                   $loader;
    private IAppRepository            $appRepository;
    private TemplateRendererInterface $templateRenderer;
    private IL10N                     $translator;
    private Diff                      $diff;

    public function __construct(
        IL10N $l10n
        , Legacy $legacy
        , ILoader $loader
        , IAppRepository $appRepository
        , TemplateRendererInterface $templateRenderer
        , IAppRenderer $appRenderer
        , Diff $diff
    ) {
        parent::__construct($appRenderer);

        $this->legacy           = $legacy;
        $this->loader           = $loader;
        $this->appRepository    = $appRepository;
        $this->templateRenderer = $templateRenderer;
        $this->translator       = $l10n;
        $this->diff             = $diff;
    }

    public function run(ServerRequestInterface $request): string {
        // We only check loadedApps if the system is
        // installed
        $loadedApps    = $this->loader->getApps();
        $installedApps = $this->appRepository->getAllApps();

        $diff = $this->diff;

        // Step 1: we remove all apps that are disabled in our db
        $loadedApps = $diff->removeDisabledApps($loadedApps, $installedApps);

        // Step 2: we determine all apps that needs to be installed
        $appsToInstall = $diff->getNewlyAddedApps($loadedApps, $installedApps);

        // Step 3: we check if one of our loaded apps has a new version
        // at this point, we can be sure that both maps contain the same
        // apps
        $appsToUpgrade = $diff->getAppsThatNeedAUpgrade($loadedApps, $installedApps);

        return $this->templateRenderer
            ->render(
                'install::install'
                , [
                    "installationHeader"                    => $this->translator->translate("Installing Apps")
                    , "installationDescription"             => $this->translator->translate("Your {$this->legacy->getApplication()->get('name')} instance is not fully installed yet. Follow the instructions below to complete the installation.")
                    , "installInstructionInstallApps"       => $this->translator->translate("The following apps are going to be installed. Please click on \"Install\" to finish the process.")
                    , "installInstructionNothingToUpdate"   => $this->translator->translate("Your {$this->legacy->getApplication()->get('name')} instance is installed. Please click on \"End\" to start using {$this->legacy->getApplication()->get('name')}.")
                    , "installInstructionUpdateApps"        => $this->translator->translate("The following apps are going to be updated. Please click on \"Install\" to finish the process.")
                    , "installInstructionInstallUpdateApps" => $this->translator->translate("The following apps are going to be installed and updated. Please click on \"Install\" to finish the process.")
                    , "installationDescriptionError"        => $this->translator->translate("It seems to be that the installer has an error and could not install or update your apps. Please consult your server admin or try installing {$this->legacy->getApplication()->get('name')} again.")
                    , "updateInstruction"                   => $this->translator->translate("The following apps are going to be updated. Please click on \"Install\" to finish the process.")
                    , "endUpdate"                           => $this->translator->translate("End")
                    , "installApps"                         => $this->translator->translate("Install")
                    , "updateApps"                          => $this->translator->translate("Update")
                    , "appsToInstall"                       => $this->hashTableToArray($appsToInstall)
                    , "appsToUpdate"                        => $this->hashTableToArray($appsToUpgrade)
                    , "installType"                         => $this->getInstallType($appsToInstall, $appsToUpgrade)
                ]
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

}
