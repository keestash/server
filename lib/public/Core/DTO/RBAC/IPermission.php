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

use doganoo\SimpleRBAC\Entity\PermissionInterface;
use KSP\Core\DTO\Entity\IJsonObject;

interface IPermission extends PermissionInterface, IJsonObject {

    public const PERMISSION_READ_USER                                   = 2;
    public const PERMISSION_READ_ALL_USER                               = 3;
    public const PERMISSION_USER_ADD                                    = 4;
    public const PERMISSION_UPDATE_USER_PROFILE                         = 5;
    public const PERMISSION_USER_EDIT                                   = 6;
    public const PERMISSION_USER_LOCK                                   = 7;
    public const PERMISSION_USER_REMOVE                                 = 8;
    public const PERMISSION_ORGANIZATION_ACTIVATE                       = 9;
    public const PERMISSION_ORGANIZATION_ADD                            = 10;
    public const PERMISSION_ORGANIZATION_GET                            = 11;
    public const PERMISSION_ORGANIZATION_UPDATE                         = 12;
    public const PERMISSION_ORGANIZATION_CHANGE                         = 13;
    public const PERMISSION_REGISTER_USER_EXIST                         = 14;
    public const PERMISSION_GENERAL_API_THUMBNAIL_GET                   = 15;
    public const PERMISSION_APPS_READ                                   = 16;
    public const PERMISSION_APPS_UPDATE                                 = 17;
    public const PERMISSION_PASSWORD_MANAGER_NODE_ATTACHMENT_READ       = 18;
    public const PERMISSION_PASSWORD_MANAGER_NODE_ATTACHMENT_REMOVE     = 19;
    public const PERMISSION_PASSWORD_MANAGER_NODE_AVATAR_UPDATE         = 20;
    public const PERMISSION_PASSWORD_MANAGER_NODE_COMMENT_ADD           = 21;
    public const PERMISSION_PASSWORD_MANAGER_NODE_COMMENT_READ          = 22;
    public const PERMISSION_PASSWORD_MANAGER_NODE_COMMENT_REMOVE        = 23;
    public const PERMISSION_PASSWORD_MANAGER_NODE_ATTACHMENT_ADD        = 24;
    public const PERMISSION_PASSWORD_MANAGER_NODE_DELETE                = 25;
    public const PERMISSION_PASSWORD_MANAGER_NODE_READ                  = 26;
    public const PERMISSION_PASSWORD_MANAGER_NODE_MOVE                  = 27;
    public const PERMISSION_PASSWORD_MANAGER_NODE_SHAREABLE_USERS       = 28;
    public const PERMISSION_PASSWORD_MANAGER_CREDENTIAL_GET_BY_NODE_ID  = 29;
    public const PERMISSION_PASSWORD_MANAGER_CREDENTIAL_PASSWORD_UPDATE = 30;
    public const PERMISSION_PASSWORD_MANAGER_CREDENTIAL_CREATE          = 31;
    public const PERMISSION_PASSWORD_MANAGER_CREDENTIAL_UPDATE          = 32;
    public const PERMISSION_PASSWORD_MANAGER_NODE_CREATE                = 33;
    public const PERMISSION_PASSWORD_MANAGER_ORGANIZATION_NODE_ADD      = 34;
    public const PERMISSION_PASSWORD_MANAGER_ORGANIZATION_NODE_REMOVE   = 35;
    public const PERMISSION_PASSWORD_MANAGER_ORGANIZATION_NODE_UPDATE   = 36;
    public const PERMISSION_PASSWORD_MANAGER_NODE_PWNED_CHART_ALL       = 37;
    public const PERMISSION_PASSWORD_MANAGER_NODE_PWNED_CHART_DETAIL    = 38;

}