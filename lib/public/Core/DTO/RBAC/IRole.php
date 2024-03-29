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

namespace KSP\Core\DTO\RBAC;

use doganoo\SimpleRBAC\Entity\RoleInterface;
use KSP\Core\DTO\Entity\IJsonObject;

interface IRole extends RoleInterface, IJsonObject {

    public const ROLE_USER_ADMIN              = 2;
    public const ROLE_NAME_USER_ADMIN         = 'USER_ADMIN';
    public const ROLE_ORGANIZATION_ADMIN      = 3;
    public const ROLE_NAME_ORGANIZATION_ADMIN = 'ORGANIZATION_ADMIN';
    public const ROLE_APP_ADMIN               = 4;
    public const ROLE_NAME_APP_ADMIN          = 'APP_ADMIN';
    public const ROLE_LDAP_ADMIN              = 6;
    public const ROLE_NAME_LDAP_ADMIN         = 'LDAP_ADMIN';

}