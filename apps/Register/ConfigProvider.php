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

namespace KSA\Register;

final class ConfigProvider {

    public const REGISTER               = '/register[/]';
    public const REGISTER_ADD           = '/register/add[/]';
    public const REGISTER_CONFIRM           = '/register/confirm';
    public const PASSWORD_REQUIREMENTS  = '/password_requirements[/]';
    public const REGISTER_CONFIGURATION = '/register/configuration[/]';

    public const USER_EXISTS_BY_USERNAME = '/user/exists/:userName[/]';
    public const USER_EXISTS_BY_MAIL     = '/user/mail/exists/:address[/]';
    public const APP_ID                  = 'register';

    public function __invoke(): array {
        return require __DIR__ . '/config/config.php';
    }

}