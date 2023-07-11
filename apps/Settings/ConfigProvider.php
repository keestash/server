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

namespace KSA\Settings;

final class ConfigProvider {

    public const APP_ID                    = 'settings';
    public const USER_GET_HASH             = '/users/get/:userHash[/]';
    public const USER_GET_ALL              = '/users/all[/]';
    public const USER_LOCK                 = '/users/lock[/]';
    public const USER_PROFILE_IMAGE_UPDATE = '/users/profile_image/update';
    public const USER_ADD                  = '/users/add[/]';
    public const USER_EDIT                 = '/users/edit';
    public const USER_REMOVE               = '/users/remove';
    public const ORGANIZATION_LIST_ALL     = '/organizations/all/[:includeInactive/][:userHash/]';
    public const ORGANIZATION_ACTIVATE     = '/organizations/activate[/]';
    public const ORGANIZATION_ADD          = '/organizations/add[/]';
    public const ORGANIZATION_BY_ID        = '/organizations/:id[/]';
    public const ORGANIZATION_UPDATE       = '/organizations/update[/]';
    public const ORGANIZATION_USER_CHANGE  = '/organizations/user/change[/]';

    public const ALLOWED_PROFILE_IMAGE_EXTENSIONS = "extensions.image.profile.allowed";

    public function __invoke(): array {
        return require __DIR__ . '/config/config.php';
    }

}