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

namespace KSA\InstallInstance\Controller;

use doganoo\PHPUtil\Log\FileLogger;
use Keestash;
use Keestash\Core\Permission\PermissionFactory;
use Keestash\Core\Service\InstallerService;
use Keestash\Core\System\Installation\Instance\LockHandler;
use KSP\Core\Controller\FullscreenAppController;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\L10N\IL10N;

class Controller extends FullscreenAppController {

    public const TEMPLATE_NAME_INSTALL_INSTANCE   = "install_instance.twig";
    public const TEMPLATE_NAME_CONFIG_PART        = "config_part.twig";
    public const TEMPLATE_NAME_DIRS_WRITABLE_PART = "dirs_writable.twig";
    public const TEMPLATE_NAME_HAS_DATA_DIRS      = "has_data_dirs.twig";

    private $permissionManager = null;
    private $templateManager   = null;
    private $lockHandler       = null;
    private $installerService  = null;

    public function __construct(
        ITemplateManager $templateManager
        , IPermissionRepository $permissionManager
        , IL10N $l10n
        , LockHandler $lockHandler
        , InstallerService $installerService
    ) {
        parent::__construct(
            $templateManager
            , $l10n
        );

        $this->templateManager   = $templateManager;
        $this->permissionManager = $permissionManager;
        $this->lockHandler       = $lockHandler;
        $this->installerService  = $installerService;

    }


    public function onCreate(...$params): void {
        parent::setPermission(
            PermissionFactory::getDefaultPermission()
        );

    }

    public function create(): void {

        $locked = $this->lockHandler->isLocked();
        $legacy = Keestash::getServer()->getLegacy();

        if (false === $locked) {
            FileLogger::info("The isntallation routine was not locked. However, we are in this class and lock the installation until we are finished");
        }

        if (true === $this->installerService->hasIdAndHash()) {
            Keestash::getServer()->getHTTPRouter()->routeTo("login");
        }

        $this->getTemplateManager()->replace(
            Controller::TEMPLATE_NAME_INSTALL_INSTANCE
            , [
                "installationHeader"        => $this->getL10N()->translate("Installation")
                , "installInstruction"      => $this->getL10N()->translate("Your {$legacy->getApplication()->get('name')} instance seems to be incomplete. Please follow the instructions below:")
                , "endUpdate"               => $this->getL10N()->translate("End Update")
                , "configurationPartHeader" => $this->getL10N()->translate("Configuration File")
                , "dirsWritablePartHeader"  => $this->getL10N()->translate("Files and Directories that are not Writable")
                , "hasDataDirsPartHeader"   => $this->getL10N()->translate("Data Directories that are missing")
                , "templates"               => json_encode([
                    "config_template"          => $this->getTemplateManager()->getRawTemplate(Controller::TEMPLATE_NAME_CONFIG_PART)
                    , "dirs_writable_template" => $this->getTemplateManager()->getRawTemplate(Controller::TEMPLATE_NAME_DIRS_WRITABLE_PART)
                    , "has_data_dirs_template" => $this->getTemplateManager()->getRawTemplate(Controller::TEMPLATE_NAME_HAS_DATA_DIRS)
                ])
                , "strings"                 => json_encode([
                    "has_data_dirs" => [
                        "nothingToUpdate" => $this->getL10N()->translate("Nothing To Update")
                    ]
                ])

            ]
        );
        parent::render(Controller::TEMPLATE_NAME_INSTALL_INSTANCE);

    }


    public function afterCreate(): void {

    }

}