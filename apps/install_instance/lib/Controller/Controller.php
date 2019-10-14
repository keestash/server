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

use doganoo\PHPUtil\FileSystem\FileHandler;
use Keestash;
use Keestash\Core\Permission\PermissionFactory;
use Keestash\Core\System\Installation\Instance\LockHandler;
use Keestash\Core\System\Installation\Verification\ConfigFileReadable;
use Keestash\Core\System\Installation\Verification\DatabaseReachable;
use Keestash\Core\System\Installation\Verification\DirsWritable;
use Keestash\Core\System\Installation\Verification\HasDataDirs;
use KSA\InstallInstance\Application\Application;
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

    public function __construct(
        ITemplateManager $templateManager
        , IPermissionRepository $permissionManager
        , IL10N $l10n
        , LockHandler $lockHandler
    ) {
        parent::__construct(
            $templateManager
            , $l10n
        );

        $this->templateManager   = $templateManager;
        $this->permissionManager = $permissionManager;
        $this->lockHandler       = $lockHandler;

    }


    public function onCreate(...$params): void {
        parent::setPermission(
            PermissionFactory::getDefaultPermission()
        );

    }

    public function create(): void {
        $fileHandler = new FileHandler(
            Keestash::getServer()->getInstallerRoot() . "instance.installation"
        );
        $content     = $fileHandler->getContent() ?? null;

        if (null === $content) {
            Keestash::getServer()->getHTTPRouter()->routeTo("login");
            return;
        }

        $content = json_decode($content, true);
        $legacy  = Keestash::getServer()->getLegacy();

        $notWritableDirs   = $this->handleDirsWritable($content);
        $config            = $this->handleConfig($content);
        $hasDataDir        = $this->handleHasDataDirs($content);
        $databaseReachable = $this->hasDatabaseReachable($content);
        $this->getTemplateManager()->replace(
            Controller::TEMPLATE_NAME_INSTALL_INSTANCE
            , [
                "installationHeader"   => $this->getL10N()->translate("Installation")
                , "installInstruction" => $this->getL10N()->translate("Your {$legacy->getApplication()->get('name')} instance seems to be incomplete. Please follow the instructions below:")
                , "endUpdate"          => $this->getL10N()->translate("End Update")
                , "header"             => ($content)
                , "configPart"         => $config
                , "dirsWritablePart"   => $notWritableDirs
                , "hasDataDirs"        => $hasDataDir
                , "databaseReachable"  => $databaseReachable
            ]
        );
        parent::render(Controller::TEMPLATE_NAME_INSTALL_INSTANCE);

    }

    private function hasDatabaseReachable(?array $content): ?string {
        if (null === $content) return null;
        $databaseReachable = $content[DatabaseReachable::class] ?? null;

        if (null === $databaseReachable) return null;

        $this->getTemplateManager()->replace(
            Controller::TEMPLATE_NAME_DATABASE_REACHABLE
            , [
                "header"                      => $this->getL10N()->translate("The database is not reachable. Please edit the config file and try it again")
                , "databaseReachable"         => $databaseReachable
                , "dbNotReachableLabel"       => $this->getL10N()->translate("Database is not reachable")
                , "dbNotReachableDescription" => $this->getL10N()->translate("Please insert the correct values and try it again")
            ]
        );

        return $this->getTemplateManager()->render(Controller::TEMPLATE_NAME_DATABASE_REACHABLE);
    }

    private function handleHasDataDirs(?array $content): ?string {
        if (null === $content) return null;
        $hasDataDirs = $content[HasDataDirs::class] ?? null;

        if (null === $hasDataDirs) return null;

        $this->getTemplateManager()->replace(
            Controller::TEMPLATE_NAME_HAS_DATA_DIRS
            , [
                "header"     => $this->getL10N()->translate("There are some files and/or dirs which are missing in your instance. Please add the following files")
                , "dataDirs" => $hasDataDirs
            ]
        );

        return $this->getTemplateManager()->render(Controller::TEMPLATE_NAME_HAS_DATA_DIRS);
    }

    private function handleDirsWritable(?array $content): ?string {
        if (null === $content) return null;
        $notWritableDirs = $content[DirsWritable::class] ?? null;
        if (null === $notWritableDirs) return null;

        $dirWritable = $notWritableDirs['dir_writable'] ?? null;
        $dirReadable = $notWritableDirs['dir_readable'] ?? null;


        $this->getTemplateManager()->replace(
            Controller::TEMPLATE_NAME_DIRS_WRITABLE_PART
            , [
                "writableSize"     => count($dirWritable)
                , "readableSize"   => count($dirReadable)
                , "writableHeader" => $this->getL10N()->translate("The following files are not writable by the webserver user. Please change the permissions and try it again")
                , "readableHeader" => $this->getL10N()->translate("The following files are not readable by the webserver user. Please change the permissions and try it again")
                , "dirsWritable"   => $dirWritable
                , "dirsReadable"   => $dirReadable
                , "submit"         => $this->getL10N()->translate("Check Again")
            ]
        );
        return $this->getTemplateManager()->render(self::TEMPLATE_NAME_DIRS_WRITABLE_PART);
    }

    private function handleConfig(?array $content): ?string {
        if (null === $content) return null;
        $config = $content[ConfigFileReadable::class] ?? null;

        if (null === $config) return null;

        $legacy = Keestash::getServer()->getLegacy();

        $this->getTemplateManager()->replace(
            Controller::TEMPLATE_NAME_CONFIG_PART
            , [
                "header"                     => $this->getL10N()->translate("Config Parameters")
                , "dbHostLabel"              => $this->getL10N()->translate("Database Host")
                , "dbHostPlaceholder"        => $this->getL10N()->translate("Database Host")
                , "dbUserLabel"              => $this->getL10N()->translate("Database User")
                , "dbUserPlaceholder"        => $this->getL10N()->translate("Database User")
                , "dbPasswordLabel"          => $this->getL10N()->translate("Database Password")
                , "dbPasswordPlaceholder"    => $this->getL10N()->translate("Database Password")
                , "dbNameLabel"              => $this->getL10N()->translate("Database Name")
                , "dbNamePlaceholder"        => $this->getL10N()->translate("Database Name")
                , "dbPortLabel"              => $this->getL10N()->translate("Database Port")
                , "dbPortPlaceholder"        => $this->getL10N()->translate("Database Port")
                , "dbCharsetLabel"           => $this->getL10N()->translate("Database Charset")
                , "dbCharsetPlaceholder"     => $this->getL10N()->translate("Database Charset")
                , "logRequestsLabel"         => $this->getL10N()->translate("Log Requests")
                , "logRequestsPlaceholder"   => $this->getL10N()->translate("Log Requests")
                , "submit"                   => $this->getL10N()->translate("Save")
                , "enabled"                  => $this->getL10N()->translate("Enabled")
                , "disabled"                 => $this->getL10N()->translate("Disabled")
                , "dbHostDescription"        => $this->getL10N()->translate(
                    "Please enter the database host where "
                    . $legacy->getApplication()->get("name")
                    . " is hosted. "
                )
                , "dbUserDescription"        => $this->getL10N()->translate(
                    "Please enter the user name for the database host where "
                    . $legacy->getApplication()->get("name")
                    . " is hosted. "
                )
                , "dbPasswordDescription"    => $this->getL10N()->translate(
                    "Please enter the password for the user name given above"
                )
                , "dbNameDescription"        => $this->getL10N()->translate(
                    "Please enter the database name"
                )
                , "dbPortDescription"        => $this->getL10N()->translate(
                    "Please enter the database port"
                )
                , "dbCharsetDescription"     => $this->getL10N()->translate(
                    "Please enter the database charset"
                )
                , "dbLogRequestsDescription" => $this->getL10N()->translate(
                    "Please sepcify whether API requests should be logged"
                )

                , "enabledValue"             => Application::LOG_REQUESTS_ENABLED
                , "disabledValue"            => Application::LOG_REQUESTS_DISABLED
                , "instructions"             => $config
            ]
        );
        return $this->getTemplateManager()->render(Controller::TEMPLATE_NAME_CONFIG_PART);
    }


    public function afterCreate(): void {

    }

}