<?php
declare(strict_types=1);

/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

use doganoo\SimpleRBAC\Entity\RoleInterface;
use Keestash\ConfigProvider as CoreConfigProvider;
use KSA\Settings\ConfigProvider;
use KSP\Core\DTO\RBAC\IPermission;
use KSP\Core\DTO\RBAC\IRole;

return [
    CoreConfigProvider::PERMISSION_MAPPING     => [
        // user
        ConfigProvider::USER_GET_HASH                => IPermission::PERMISSION_READ_USER
        , ConfigProvider::USER_GET_ALL               => IPermission::PERMISSION_READ_USER
        , ConfigProvider::USER_PROFILE_IMAGE_UPDATE  => IPermission::PERMISSION_UPDATE_USER_PROFILE
        , ConfigProvider::USER_ADD                   => IPermission::PERMISSION_USER_ADD
        , ConfigProvider::USER_EDIT                  => IPermission::PERMISSION_USER_EDIT
        , ConfigProvider::USER_LOCK                  => IPermission::PERMISSION_USER_LOCK
        , ConfigProvider::USER_REMOVE                => IPermission::PERMISSION_USER_REMOVE
        , ConfigProvider::USER_PROFILE_CONFIGURATION => IPermission::PERMISSION_SETTINGS_USER_PROFILE_CONFIGURATION

        // organization
        , ConfigProvider::ORGANIZATION_ACTIVATE      => IPermission::PERMISSION_ORGANIZATION_ACTIVATE
        , ConfigProvider::ORGANIZATION_ADD           => IPermission::PERMISSION_ORGANIZATION_ADD
        , ConfigProvider::ORGANIZATION_BY_ID         => IPermission::PERMISSION_ORGANIZATION_GET
        , ConfigProvider::ORGANIZATION_LIST_ALL      => IPermission::PERMISSION_ORGANIZATION_GET
        , ConfigProvider::ORGANIZATION_UPDATE        => IPermission::PERMISSION_ORGANIZATION_UPDATE
        , ConfigProvider::ORGANIZATION_USER_CHANGE   => IPermission::PERMISSION_ORGANIZATION_CHANGE
    ]
    , CoreConfigProvider::PERMISSION_FREE      => []
    , CoreConfigProvider::PERMISSION_LIST      => [
        IPermission::PERMISSION_READ_USER                             => IPermission::PERMISSION_NAME_READ_USER
        , IPermission::PERMISSION_USERS_EDIT_OTHER_USERS              => IPermission::PERMISSION_NAME_USERS_EDIT_OTHER_USERS
        , IPermission::PERMISSION_UPDATE_USER_PROFILE                 => IPermission::PERMISSION_NAME_UPDATE_USER_PROFILE
        , IPermission::PERMISSION_USER_ADD                            => IPermission::PERMISSION_NAME_USER_ADD
        , IPermission::PERMISSION_USER_EDIT                           => IPermission::PERMISSION_NAME_USER_EDIT
        , IPermission::PERMISSION_USER_LOCK                           => IPermission::PERMISSION_NAME_USER_LOCK
        , IPermission::PERMISSION_USER_REMOVE                         => IPermission::PERMISSION_NAME_USER_REMOVE
        , IPermission::PERMISSION_ORGANIZATION_ACTIVATE               => IPermission::PERMISSION_NAME_ORGANIZATION_ACTIVATE
        , IPermission::PERMISSION_ORGANIZATION_ADD                    => IPermission::PERMISSION_NAME_ORGANIZATION_ADD
        , IPermission::PERMISSION_ORGANIZATION_GET                    => IPermission::PERMISSION_NAME_ORGANIZATION_GET
        , IPermission::PERMISSION_ORGANIZATION_UPDATE                 => IPermission::PERMISSION_NAME_ORGANIZATION_UPDATE
        , IPermission::PERMISSION_ORGANIZATION_CHANGE                 => IPermission::PERMISSION_NAME_ORGANIZATION_CHANGE
        , IPermission::PERMISSION_SETTINGS_USER_PROFILE_CONFIGURATION => IPermission::PERMISSION_NAME_SETTINGS_USER_PROFILE_CONFIGURATION
    ]
    , CoreConfigProvider::ROLE_PERMISSION_LIST => [
        RoleInterface::DEFAULT_NAME   => [
            IPermission::PERMISSION_READ_USER
            , IPermission::PERMISSION_UPDATE_USER_PROFILE
            , IPermission::PERMISSION_USER_EDIT
            , IPermission::PERMISSION_SETTINGS_USER_PROFILE_CONFIGURATION
        ]
        , IRole::ROLE_NAME_USER_ADMIN => [
            IPermission::PERMISSION_USER_ADD
            , IPermission::PERMISSION_USER_LOCK
            , IPermission::PERMISSION_USER_REMOVE
            , IPermission::PERMISSION_ORGANIZATION_ACTIVATE
            , IPermission::PERMISSION_ORGANIZATION_ADD
            , IPermission::PERMISSION_ORGANIZATION_GET
            , IPermission::PERMISSION_ORGANIZATION_UPDATE
            , IPermission::PERMISSION_ORGANIZATION_CHANGE
            , IPermission::PERMISSION_USERS_EDIT_OTHER_USERS
        ]
    ]
];