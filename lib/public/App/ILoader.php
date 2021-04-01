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

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;

interface ILoader {

    public const APP_NAME_INSTALL          = "Install";
    public const APP_NAME_TNC              = "TNC";
    public const APP_NAME_ABOUT            = "About";
    public const APP_NAME_USERS            = "Users";
    public const APP_NAME_LOGIN            = "Login";
    public const APP_NAME_GENERAL_API      = "GeneralApi";
    public const APP_NAME_APPS             = "Apps";
    public const APP_NAME_LOGOUT           = "Logout";
    public const APP_NAME_INSTALL_INSTANCE = "InstallInstance";
    public const APP_NAME_FORGOT_PASSWORD  = "ForgotPassword";
    public const APP_NAME_REGISTER         = "Register";

    public const DIR_NAME_FRONTEND = "frontend";

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
