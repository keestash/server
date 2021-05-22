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

namespace KSA\PasswordManager;


final class ConfigProvider {

    public const PASSWORD_MANAGER_ATTACHMENTS_VIEW     = "/password_manager/attachments/view/:fileId[/]";
    public const PASSWORD_MANAGER                      = "/password_manager[/]";
    public const PASSWORD_MANAGER_PUBLIC_SHARE_SINGLE  = "/s/:hash[/]";
    public const PASSWORD_MANAGER_PUBLIC_SHARE_DECRYPT = "/password_manager/public_share/decrypt/:hash[/]";
    public const APP_ID                                = 'passwordManager';

    public function __invoke(): array {
        return require __DIR__ . '/config/config.php';
    }

}