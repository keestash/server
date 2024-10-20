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

use Keestash\ConfigProvider as CoreConfigProvider;
use KSA\PasswordManager\ConfigProvider;

return [
    CoreConfigProvider::ROUTES        =>
        array_merge(
        // order matters
            require __DIR__ . '/router/share/public.php',
            require __DIR__ . '/router/share/regular.php',
            require __DIR__ . '/router/attachments.php',
            require __DIR__ . '/router/folder.php',
            require __DIR__ . '/router/node.php',
            require __DIR__ . '/router/organization.php',
            require __DIR__ . '/router/activity.php',
            require __DIR__ . '/router/credential.php',
            []
        ),
    CoreConfigProvider::PUBLIC_ROUTES => [
        ConfigProvider::PASSWORD_MANAGER_ATTACHMENTS_DOWNLOAD,
        ConfigProvider::PASSWORD_MANAGER_PUBLIC_SHARE_DECRYPT,
    ]
];
