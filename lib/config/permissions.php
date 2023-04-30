<?php
declare(strict_types=1);

/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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
use Keestash\ConfigProvider;
use KSP\Core\DTO\RBAC\IRole;

return [
    ConfigProvider::ROLE_LIST => [
        RoleInterface::DEFAULT           => RoleInterface::DEFAULT_NAME
        , IRole::ROLE_USER_ADMIN         => IRole::ROLE_NAME_USER_ADMIN
        , IRole::ROLE_ORGANIZATION_ADMIN => IRole::ROLE_NAME_ORGANIZATION_ADMIN
        , IRole::ROLE_APP_ADMIN          => IRole::ROLE_NAME_APP_ADMIN
        , IRole::ROLE_LDAP_ADMIN         => IRole::ROLE_NAME_LDAP_ADMIN
    ]
];