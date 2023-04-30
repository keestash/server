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
use KSA\GeneralApi\ConfigProvider;
use KSP\Core\DTO\RBAC\IPermission;

return [
    CoreConfigProvider::PERMISSION_MAPPING     => [
        ConfigProvider::THUMBNAIL_BY_EXTENSION => IPermission::PERMISSION_GENERAL_API_THUMBNAIL_GET
    ]
    , CoreConfigProvider::PERMISSION_FREE      => []
    , CoreConfigProvider::PERMISSION_LIST      => [
        IPermission::PERMISSION_GENERAL_API_THUMBNAIL_GET => IPermission::PERMISSION_NAME_GENERAL_API_THUMBNAIL_GET
    ]
    , CoreConfigProvider::ROLE_PERMISSION_LIST => [
        RoleInterface::DEFAULT_NAME => [
            IPermission::PERMISSION_GENERAL_API_THUMBNAIL_GET
        ]
    ]
];