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

namespace KSA\Login;

final class ConfigProvider {

    public const LOGIN_SUBMIT      = '/login/submit';
    public const LOGIN_KEY         = '/login/key';
    public const LOGOUT_SUBMIT     = '/logout/submit';
    public const APP_CONFIGURATION = '/app/configuration';
    public const APP_ID            = 'login';

    public function __invoke(): array {
        return require __DIR__ . '/config/config.php';
    }

}