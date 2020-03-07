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

namespace Keestash\Core\Manager\RouterManager\Router;

use doganoo\PHPUtil\Log\FileLogger;
use doganoo\PHPUtil\Util\ClassUtil;
use Exception;
use Keestash;
use Keestash\Exception\KSException;

class Helper {

    private const ROUTE_INSTALL_INSTANCE               = "install_instance";
    private const ROUTE_INSTALL_INSTANCE_UPDATE_CONFIG = "install_instance/update_config/";
    private const ROUTE_INSTALL_INSTANCE_DIRS_WRITABLE = "install_instance/dirs_writable/";
    private const ROUTE_INSTALL_INSTANCE_CONFIG_DATA   = "install_instance/config_data/";
    private const ROUTE_INSTALL_INSTANCE_END_UPDATE    = "install_instance/end_update/";
    private const ROUTE_INSTALL_INSTANCE_HAS_DATA_DIRS = "install_instance/has_data_dirs/";
    private const ROUTE_LOGIN_SUBMIT                   = "login/submit";

    private function __construct() {
    }

    public function __clone() {
    }

    public static function routesToInstallation(): bool {
        $router               = Keestash::getServer()->getRouter();
        $className            = ClassUtil::getClassName($router);
        $routesToInstallation = false;

        switch ($className) {
            case HTTPRouter::class:
                /** @var HTTPRouter $router */
                $routesToInstallation = Helper::handleHttp($router);
                break;
            case APIRouter::class:
                /** @var APIRouter $router */
                $routesToInstallation = Helper::handleApi($router);
                break;
            default:
                throw new KSException("could not identify class");
        }

        return $routesToInstallation;
    }

    private static function handleHttp(HTTPRouter $router): bool {
        if (false === $router->hasRoute(Helper::ROUTE_INSTALL_INSTANCE)) return false;
        if ($router->getRouteName() !== Helper::ROUTE_INSTALL_INSTANCE) return false;
        return true;
    }

    private static function handleApi(APIRouter $router) {

        try {
            $name = $router->getRouteName();
            return in_array(
                $name
                , [
                    Helper::ROUTE_INSTALL_INSTANCE_UPDATE_CONFIG
                    , Helper::ROUTE_INSTALL_INSTANCE_DIRS_WRITABLE
                    , Helper::ROUTE_INSTALL_INSTANCE_CONFIG_DATA
                    , Helper::ROUTE_INSTALL_INSTANCE_END_UPDATE
                    , Helper::ROUTE_INSTALL_INSTANCE_HAS_DATA_DIRS
                    , Helper::ROUTE_LOGIN_SUBMIT
                ]
            );
        } catch (Exception $e) {
            // we do not do anything here
            // we know that there is no route and
            // return false
            FileLogger::debug($e->getTraceAsString());
            return false;
        }

    }

    public static function buildWebRoute(string $base): string {
        return Keestash::getBaseURL(true, true) . "/" . $base;
    }

}