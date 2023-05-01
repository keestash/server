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
    public const TEST_PATH               = 'path.test';
    public const DATA_PATH               = 'path.data';
    public const INSTANCE_PATH           = 'path.instance';
    public const APP_PATH                = 'path.app';
    public const ENVIRONMENT_KEY         = 'keestash.environment';
    public const ENVIRONMENT_API         = 'api.environment';
    public const ENVIRONMENT_WEB         = 'web.environment';
    public const ENVIRONMENT_CONSOLE     = 'console.environment';
    public const ENVIRONMENT_UNIT_TEST   = 'test.unit.environment';
    public const ENVIRONMENT_SAAS        = 'saas.environment';
    public const INSTALL_INSTANCE_ROUTE  = 'install_instance';
    public const INSTALL_INSTANCE_ROUTES = 'routes.instance.install';
    public const INSTALL_APPS_ROUTES     = 'routes.instance.install';
    public const ROUTES                  = 'routes';
    public const COMMANDS                = 'commands';
    public const SETTINGS                = 'settings';
    public const SETTINGS_ORDER          = 'order.settings';
    public const SETTINGS_NAME           = 'name.settings';
    public const PUBLIC_ROUTES           = 'routes.public';
    public const WEB_ROUTER_STYLESHEETS  = 'stylesheets.router.web';
    public const EVENTS                  = 'events';
    public const WEB_ROUTER_SCRIPTS      = 'scripts.router.web';
    /** @deprecated */
    public const WEB_ROUTER           = 'router.web';
    public const API_ROUTER           = 'router.api';
    public const DEPENDENCIES         = 'dependencies';
    public const FACTORIES            = 'factories';
    public const ALIASES              = 'aliases';
    public const TEMPLATES            = 'templates';
    public const PATHS                = 'paths';
    public const COUNTRY_CODES        = 'codes.country';
    public const COUNTRY_PREFIXES     = 'prefixes.country';
    public const PERMISSIONS          = 'permissions';
    public const PERMISSION_MAPPING   = 'mapping.permissions';
    public const PERMISSION_FREE      = 'free.permissions';
    public const PERMISSION_LIST      = 'list.permission';
    public const ROLE_LIST            = 'list.role';
    public const ROLE_PERMISSION_LIST = 'list.permission.role';

    public const APP_LIST  = 'list.app';
    public const APP_ID    = 'id.app';
    public const APP_ORDER = 'order.app';
    public const APP_NAME  = 'name.app';
    /** @deprecated */
    public const APP_BASE_ROUTE   = 'route.base.app';
    public const APP_VERSION      = 'version.app';
    public const REGISTER_ENABLED = 'enabled.register';

    public const LOG_REQUESTS_ENABLED  = "enabled";
    public const LOG_REQUESTS_DISABLED = "disabled";

    public const DEFAULT_USER_LIFETIME = 60 * 60;


    public function __invoke(): array {
        return require __DIR__ . '/../config/config.php';
    }

}