<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace Keestash;

final class ConfigProvider {

    public const INSTANCE_DB_PATH        = 'path.db.instance';
    public const CONFIG_PATH             = 'path.config';
    public const ASSET_PATH              = 'path.asset';
    public const IMAGE_PATH              = 'path.image';
    public const PHINX_PATH              = 'path.phinx';
    public const DATA_PATH               = 'path.data';
    public const INSTANCE_PATH           = 'path.instance';
    public const APP_PATH                = 'path.app';
    public const ENVIRONMENT_KEY         = 'keestash.environment';
    public const ENVIRONMENT_API         = 'api.environment';
    public const ENVIRONMENT_WEB         = 'web.environment';
    public const ENVIRONMENT_CONSOLE     = 'console.environment';
    public const INSTALL_INSTANCE_ROUTE  = 'install_instance';
    public const INSTALL_APPS_ROUTE      = 'install_apps';
    public const INSTALL_LOGIN_ROUTE     = 'login';
    public const INSTALL_INSTANCE_ROUTES = 'routes.instance.install';
    public const INSTALL_APPS_ROUTES     = 'routes.instance.install';

    public function __invoke(): array {
        return require __DIR__ . '/../config/config.php';
    }

}