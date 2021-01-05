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

namespace KSP\App;

use Composer\Autoload\ClassLoader;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use KSP\Core\ILogger\ILogger;

interface ILoader {

    public const APP_NAME_ACCOUNT          = "account";
    public const APP_NAME_INSTALL          = "install";
    public const APP_NAME_TNC              = "tnc";
    public const APP_NAME_ABOUT            = "about";
    public const APP_NAME_USERS            = "users";
    public const APP_NAME_GENERAL_VIEW     = "general_view";
    public const APP_NAME_LOGIN            = "login";
    public const APP_NAME_MAINTENANCE      = "maintenance";
    public const APP_NAME_PROMOTION        = "promotion";
    public const APP_NAME_GENERAL_API      = "general_api";
    public const APP_NAME_APPS             = "apps";
    public const APP_NAME_LOGOUT           = "logout";
    public const APP_NAME_INSTALL_INSTANCE = "install_instance";
    public const APP_NAME_FORGOT_PASSWORD  = "forgot_password";
    public const APP_NAME_REGISTER         = "register";

    public const DIR_NAME_FRONTEND = "frontend";

    public function __construct(
        ClassLoader $classLoader,
        ILogger $logger,
        string $appRoot
    );

    public function loadApps(): void;

    public function loadAppsAndFlush(): void;

    public function loadCoreApps(): void;

    public function loadCoreAppsAndFlush(): void;

    public function loadApp(string $appId): bool;

    public function getApps(): HashTable;

    public function hasApp(string $name): bool;

    public function getDefaultApp(): ?IApp;

    public function unloadApp(string $key): bool;

    public function flush(): void;

}
