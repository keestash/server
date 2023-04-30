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

use Keestash\ConfigProvider as CoreConfigProvider;
use KSA\Register\ConfigProvider;
use KSP\Core\DTO\RBAC\IPermission;
use KSP\Core\DTO\RBAC\IRole;

return [
    CoreConfigProvider::PERMISSION_MAPPING     => [
        ConfigProvider::USER_EXISTS_BY_USERNAME => IPermission::PERMISSION_REGISTER_USER_EXIST
        , ConfigProvider::USER_EXISTS_BY_MAIL   => IPermission::PERMISSION_REGISTER_USER_EXIST
    ]
    , CoreConfigProvider::PERMISSION_FREE      => [
        ConfigProvider::REGISTER_ADD
        , ConfigProvider::PASSWORD_REQUIREMENTS
    ]
    , CoreConfigProvider::PERMISSION_LIST      => [
        IPermission::PERMISSION_REGISTER_USER_EXIST => IPermission::PERMISSION_NAME_REGISTER_USER_EXIST
    ]
    , CoreConfigProvider::ROLE_PERMISSION_LIST => [
        IRole::ROLE_NAME_USER_ADMIN => [
            IPermission::PERMISSION_REGISTER_USER_EXIST
        ]
    ]
];