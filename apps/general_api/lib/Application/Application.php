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

namespace KSA\GeneralApi\Application;

use Keestash;
use Keestash\Core\Service\Phinx\Migrator;
use Keestash\Core\Service\Stylesheet\Compiler as StylesheetCompiler;
use KSA\general_api\lib\Api\UserList;
use KSA\GeneralApi\Api\MinimumCredential;
use KSA\GeneralApi\Api\Template\GetAll;
use KSA\GeneralApi\Api\Thumbnail\File;
use KSA\GeneralApi\Command\Migration\MigrateApps;
use KSA\GeneralApi\Command\QualityTool\ClearBundleJS;
use KSA\GeneralApi\Command\QualityTool\PHPStan;
use KSA\GeneralApi\Command\Stylesheet\Compiler;
use KSP\Core\Manager\RouterManager\IRouterManager;

/**
 * Class Application
 *
 * @package Keestash\Api\Core
 */
class Application extends \Keestash\App\Application {

    public const PASSWORD_REQUIREMENTS = "password_requirements/";
    public const ALL_USERS             = "users/all/{type}/";
    public const FILE_ICONS            = "icon/file/get/{extension}/";
    public const FRONTEND_TEMPLATES    = "frontend_templates/all/";
    public const FRONTEND_STRINGS      = "frontend_strings/all/";

    public function register(): void {

        $this->registerApiRoute(
            Application::PASSWORD_REQUIREMENTS
            , MinimumCredential::class
            , [IRouterManager::POST]
        );

        $this->registerApiRoute(
            Application::ALL_USERS
            , UserList::class
            , [IRouterManager::GET]
        );

        $this->registerApiRoute(
            Application::FRONTEND_TEMPLATES
            , GetAll::class
            , [IRouterManager::GET]
        );

        $this->registerApiRoute(
            Application::FRONTEND_STRINGS
            , \KSA\GeneralApi\Api\String\GetAll::class
            , [IRouterManager::GET]
        );

        $this->registerApiRoute(
            Application::FILE_ICONS
            , File::class
            , [IRouterManager::GET]
        );

        $this->registerPublicApiRoute(
            Application::PASSWORD_REQUIREMENTS
        );
        $this->registerPublicApiRoute(
            Application::FRONTEND_STRINGS
        );

        $this->registerPublicApiRoute(
            Application::FILE_ICONS
        );

        $this->registerCommands();
    }

    private function registerCommands(): void {
        $this->registerCommand(
            new MigrateApps(
                Keestash::getServer()->query(Migrator::class)
            )
        );
        $this->registerCommand(
            new PHPStan(
                Keestash::getServer()->getServerRoot()
            )
        );

        $this->registerCommand(
            new ClearBundleJS(
                Keestash::getServer()->getServerRoot()
                , Keestash::getServer()->getAppRoot()
            )
        );

        $this->registerCommand(
            new Compiler(
                Keestash::getServer()->query(StylesheetCompiler::class)
                , Keestash::getServer()->getSCSSRoot()
            )
        );

    }

}
