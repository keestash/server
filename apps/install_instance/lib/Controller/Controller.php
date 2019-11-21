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
    public const TEMPLATE_NAME_DATABASE_REACHABLE = "database_reachable.twig";

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

        if (false === $locked) {
            FileLogger::info("The isntallation routine was not locked. However, we are in this class and lock the installation until we are finished");
        }

        $legacy = Keestash::getServer()->getLegacy();

        $this->getTemplateManager()->replace(
            Controller::TEMPLATE_NAME_INSTALL_INSTANCE
            , [
                "installationHeader"        => $this->getL10N()->translate("Installation")
                , "installInstruction"      => $this->getL10N()->translate("Your {$legacy->getApplication()->get('name')} instance seems to be incomplete. Please follow the instructions below:")
                , "endUpdate"               => $this->getL10N()->translate("End Update")
                , "configurationPartHeader" => $this->getL10N()->translate("Configuration File")
                , "config_template"         => $this->getTemplateManager()->getRawTemplate(Controller::TEMPLATE_NAME_CONFIG_PART)
                , "strings"                 => json_encode([
                    "config" => [
                        "dbHostLabel"                => $this->getL10N()->translate("Host")
                        , "dbHostPlaceholder"        => $this->getL10N()->translate("Host")
                        , "dbHostDescription"        => $this->getL10N()->translate("The server address where the database is hosted")
                        , "dbUserLabel"              => $this->getL10N()->translate("User")
                        , "dbUserPlaceholder"        => $this->getL10N()->translate("User")
                        , "dbUserDescription"        => $this->getL10N()->translate("The username used to connect to the database")
                        , "dbPasswordLabel"          => $this->getL10N()->translate("Password")
                        , "dbPasswordPlaceholder"    => $this->getL10N()->translate("Password")
                        , "dbPasswordDescription"    => $this->getL10N()->translate("The usernames password used to connect to the database")
                        , "dbNameLabel"              => $this->getL10N()->translate("Database")
                        , "dbNamePlaceholder"        => $this->getL10N()->translate("Database")
                        , "dbNameDescription"        => $this->getL10N()->translate("The database name")
                        , "dbPortLabel"              => $this->getL10N()->translate("Port")
                        , "dbPortPlaceholder"        => $this->getL10N()->translate("Port")
                        , "dbPortDescription"        => $this->getL10N()->translate("The port used to connect to the database")
                        , "dbCharsetLabel"           => $this->getL10N()->translate("Charset")
                        , "dbCharsetPlaceholder"     => $this->getL10N()->translate("Charset")
                        , "dbCharsetDescription"     => $this->getL10N()->translate("The databases charset")
                        , "logRequestsLabel"         => $this->getL10N()->translate("Log Requests")
                        , "enabledValue"             => $this->getL10N()->translate("enabled")
                        , "enabled"                  => $this->getL10N()->translate("enabled")
                        , "disabledValue"            => $this->getL10N()->translate("disabled")
                        , "disabled"                 => $this->getL10N()->translate("disabled")
                        , "dbLogRequestsDescription" => $this->getL10N()->translate("Whether API logs should be logged")
                        , "submit"                   => $this->getL10N()->translate("Save")
                        , "nothingToUpdate"          => $this->getL10N()->translate("Nothing To Update")
                    ]
                ])

            ]
        );
        parent::render(Controller::TEMPLATE_NAME_INSTALL_INSTANCE);

    }


    public function afterCreate(): void {

    }

}