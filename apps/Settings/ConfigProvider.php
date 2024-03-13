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

    public const string APP_ID                     = 'settings';
    public const string USER_GET_HASH              = '/users/get/:userHash[/]';
    public const string USER_GET_ALL               = '/users/all[/]';
    public const string USER_LOCK                  = '/users/lock[/]';
    public const string USER_PROFILE_IMAGE_UPDATE  = '/users/profile_image/update';
    public const string USER_ADD                   = '/users/add[/]';
    public const string USER_EDIT                  = '/users/edit';
    public const string USER_REMOVE                = '/users/remove';
    public const string USER_UPDATE_PASSWORD       = '/users/update-password';
    public const string USER_PROFILE_CONFIGURATION = '/users/profile/configuration';
    public const string ORGANIZATION_LIST_ALL      = '/organizations/all/[:includeInactive/][:userHash/]';
    public const string ORGANIZATION_ACTIVATE      = '/organizations/activate[/]';
    public const string ORGANIZATION_ADD           = '/organizations/add[/]';
    public const string ORGANIZATION_BY_ID         = '/organizations/:id[/]';
    public const string ORGANIZATION_UPDATE        = '/organizations/update[/]';
    public const string ORGANIZATION_USER_CHANGE   = '/organizations/user/change[/]';

    public const string ALLOWED_PROFILE_IMAGE_EXTENSIONS = "extensions.image.profile.allowed";

    public function __invoke(): array {
        return require __DIR__ . '/config/config.php';
    }

}
