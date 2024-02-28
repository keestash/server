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

    public const string  INSTANCE_DB_PATH        = 'path.db.instance';
    public const string  CONFIG_PATH             = 'path.config';
    public const string  ASSET_PATH              = 'path.asset';
    public const string  IMAGE_PATH              = 'path.image';
    public const string  PHINX_PATH              = 'path.phinx';
    public const string  TEST_PATH               = 'path.test';
    public const string  DATA_PATH               = 'path.data';
    public const string  INSTANCE_PATH           = 'path.instance';
    public const string  APP_PATH                = 'path.app';
    public const string  ENVIRONMENT_KEY         = 'keestash.environment';
    public const string  ENVIRONMENT_API         = 'api.environment';
    public const string  ENVIRONMENT_WEB         = 'web.environment';
    public const string  ENVIRONMENT_CONSOLE     = 'console.environment';
    public const string  ENVIRONMENT_UNIT_TEST   = 'test.unit.environment';
    public const string  ENVIRONMENT_SAAS        = 'saas.environment';
    public const string  INSTALL_INSTANCE_ROUTES = 'routes.instance.install';
    public const string  ROUTES                  = 'routes';
    public const string  COMMANDS                = 'commands';
    public const string  SETTINGS                = 'settings';
    public const string  SETTINGS_ORDER          = 'order.settings';
    public const string  SETTINGS_NAME           = 'name.settings';
    public const string  PUBLIC_ROUTES           = 'routes.public';
    public const string  WEB_ROUTER_STYLESHEETS  = 'stylesheets.router.web';
    public const string  EVENTS                  = 'events';
    public const string  WEB_ROUTER_SCRIPTS      = 'scripts.router.web';
    public const string  METRIC_DISALLOW_LIST    = 'list.disallow.metric';
    /** @deprecated */
    public const string  WEB_ROUTER           = 'router.web';
    public const string  API_ROUTER           = 'router.api';
    public const string  DEPENDENCIES         = 'dependencies';
    public const string  FACTORIES            = 'factories';
    public const string  ALIASES              = 'aliases';
    public const string  INVOKABLES           = 'invokables';
    public const string  TEMPLATES            = 'templates';
    public const string  PATHS                = 'paths';
    public const string  COUNTRY_CODES        = 'codes.country';
    public const string  COUNTRY_PREFIXES     = 'prefixes.country';
    public const string  PERMISSIONS          = 'permissions';
    public const string  RESPONSE_CODES       = 'codes.response';
    public const string  PERMISSION_MAPPING   = 'mapping.permissions';
    public const string  PERMISSION_FREE      = 'free.permissions';
    public const string  PERMISSION_LIST      = 'list.permission';
    public const string  ROLE_LIST            = 'list.role';
    public const string  ROLE_PERMISSION_LIST = 'list.permission.role';

    public const string  APP_LIST              = 'list.app';
    public const string  APP_ID                = 'id.app';
    public const string  APP_ORDER             = 'order.app';
    public const string  APP_NAME              = 'name.app';
    public const string  APP_VERSION           = 'version.app';
    public const string  REGISTER_ENABLED      = 'enabled.register';
    public const string  ACCOUNT_RESET_ENABLED = 'enabled.reset.account';

    public const string  LOG_REQUESTS_ENABLED  = "enabled";
    public const string  LOG_REQUESTS_DISABLED = "disabled";

    public const int  DEFAULT_USER_LIFETIME = 60 * 60;

    public const string  PING_ROUTE = '/ping';

    public function __invoke(): array {
        return require __DIR__ . '/../config/config.php';
    }

}
