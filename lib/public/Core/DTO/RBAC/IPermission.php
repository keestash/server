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

    public const PERMISSION_READ_USER                                       = 2;
    public const PERMISSION_NAME_READ_USER                                  = 'READ_USER';
    public const PERMISSION_READ_ALL_USER                                   = 3;
    public const PERMISSION_USER_ADD                                        = 4;
    public const PERMISSION_NAME_USER_ADD                                   = 'USER_ADD';
    public const PERMISSION_UPDATE_USER_PROFILE                             = 5;
    public const PERMISSION_NAME_UPDATE_USER_PROFILE                        = 'UPDATE_USER_PROFILE';
    public const PERMISSION_USER_EDIT                                       = 6;
    public const PERMISSION_NAME_USER_EDIT                                  = 'USER_EDIT';
    public const PERMISSION_USER_LOCK                                       = 7;
    public const PERMISSION_NAME_USER_LOCK                                  = 'USER_LOCK';
    public const PERMISSION_USER_REMOVE                                     = 8;
    public const PERMISSION_NAME_USER_REMOVE                                = 'USER_REMOVE';
    public const PERMISSION_ORGANIZATION_ACTIVATE                           = 9;
    public const PERMISSION_NAME_ORGANIZATION_ACTIVATE                      = 'ORGANIZATION_ACTIVATE';
    public const PERMISSION_ORGANIZATION_ADD                                = 10;
    public const PERMISSION_NAME_ORGANIZATION_ADD                           = 'ORGANIZATION_ADD';
    public const PERMISSION_ORGANIZATION_GET                                = 11;
    public const PERMISSION_NAME_ORGANIZATION_GET                           = 'ORGANIZATION_GET';
    public const PERMISSION_ORGANIZATION_UPDATE                             = 12;
    public const PERMISSION_NAME_ORGANIZATION_UPDATE                        = 'ORGANIZATION_UPDATE';
    public const PERMISSION_ORGANIZATION_CHANGE                             = 13;
    public const PERMISSION_NAME_ORGANIZATION_CHANGE                        = 'ORGANIZATION_CHANGE';
    public const PERMISSION_REGISTER_USER_EXIST                             = 14;
    public const PERMISSION_NAME_REGISTER_USER_EXIST                        = 'REGISTER_USER_EXIST';
    public const PERMISSION_GENERAL_API_THUMBNAIL_GET                       = 15;
    public const PERMISSION_NAME_GENERAL_API_THUMBNAIL_GET                  = 'GENERAL_API_THUMBNAIL_GET';
    public const PERMISSION_APPS_READ                                       = 16;
    public const PERMISSION_APPS_UPDATE                                     = 17;
    public const PERMISSION_PASSWORD_MANAGER_NODE_ATTACHMENT_READ           = 18;
    public const PERMISSION_NAME_PASSWORD_MANAGER_NODE_ATTACHMENT_READ      = 'PASSWORD_MANAGER_NODE_ATTACHMENT_READ';
    public const PERMISSION_PASSWORD_MANAGER_NODE_ATTACHMENT_REMOVE         = 19;
    public const PERMISSION_NAME_PASSWORD_MANAGER_NODE_ATTACHMENT_REMOVE    = 'PASSWORD_MANAGER_NODE_ATTACHMENT_REMOVE';
    public const PERMISSION_PASSWORD_MANAGER_NODE_AVATAR_UPDATE             = 20;
    public const PERMISSION_NAME_PASSWORD_MANAGER_NODE_AVATAR_UPDATE        = 'PASSWORD_MANAGER_NODE_AVATAR_UPDATE';
    public const PERMISSION_PASSWORD_MANAGER_NODE_COMMENT_ADD               = 21;
    public const PERMISSION_NAME_PASSWORD_MANAGER_NODE_COMMENT_ADD          = 'PASSWORD_MANAGER_NODE_COMMENT_ADD';
    public const PERMISSION_PASSWORD_MANAGER_NODE_COMMENT_READ              = 22;
    public const PERMISSION_NAME_PASSWORD_MANAGER_NODE_COMMENT_READ         = 'PASSWORD_MANAGER_NODE_COMMENT_READ';
    public const PERMISSION_PASSWORD_MANAGER_NODE_COMMENT_REMOVE            = 23;
    public const PERMISSION_NAME_PASSWORD_MANAGER_NODE_COMMENT_REMOVE       = 'PASSWORD_MANAGER_NODE_COMMENT_REMOVE';
    public const PERMISSION_PASSWORD_MANAGER_NODE_ATTACHMENT_ADD            = 24;
    public const PERMISSION_NAME_PASSWORD_MANAGER_NODE_ATTACHMENT_ADD       = 'PASSWORD_MANAGER_NODE_ATTACHMENT_ADD';
    public const PERMISSION_PASSWORD_MANAGER_NODE_DELETE                    = 25;
    public const PERMISSION_NAME_PASSWORD_MANAGER_NODE_DELETE               = 'PASSWORD_MANAGER_NODE_DELETE';
    public const PERMISSION_PASSWORD_MANAGER_NODE_READ                      = 26;
    public const PERMISSION_NAME_PASSWORD_MANAGER_NODE_READ                 = 'PASSWORD_MANAGER_NODE_READ';
    public const PERMISSION_PASSWORD_MANAGER_NODE_MOVE                      = 27;
    public const PERMISSION_NAME_PASSWORD_MANAGER_NODE_MOVE                 = 'PASSWORD_MANAGER_NODE_MOVE';
    public const PERMISSION_PASSWORD_MANAGER_NODE_SHAREABLE_USERS           = 28;
    public const PERMISSION_NAME_PASSWORD_MANAGER_NODE_SHAREABLE_USERS      = 'PASSWORD_MANAGER_NODE_SHAREABLE_USERS';
    public const PERMISSION_PASSWORD_MANAGER_CREDENTIAL_GET_BY_NODE_ID      = 29;
    public const PERMISSION_NAME_PASSWORD_MANAGER_CREDENTIAL_GET_BY_NODE_ID = 'PASSWORD_MANAGER_CREDENTIAL_GET_BY_NODE_ID';
    public const PERMISSION_PASSWORD_MANAGER_CREDENTIAL_PASSWORD_UPDATE     = 30;
    public const PERMISSION_PASSWORD_MANAGER_CREDENTIAL_CREATE              = 31;
    public const PERMISSION_NAME_PASSWORD_MANAGER_CREDENTIAL_CREATE         = 'PASSWORD_MANAGER_CREDENTIAL_CREATE';
    public const PERMISSION_PASSWORD_MANAGER_CREDENTIAL_UPDATE              = 32;
    public const PERMISSION_NAME_PASSWORD_MANAGER_CREDENTIAL_UPDATE         = 'PASSWORD_MANAGER_CREDENTIAL_UPDATE';
    public const PERMISSION_PASSWORD_MANAGER_NODE_CREATE                    = 33;
    public const PERMISSION_NAME_PASSWORD_MANAGER_NODE_CREATE               = 'PASSWORD_MANAGER_NODE_CREATE';
    public const PERMISSION_PASSWORD_MANAGER_ORGANIZATION_NODE_ADD          = 34;
    public const PERMISSION_NAME_PASSWORD_MANAGER_ORGANIZATION_NODE_ADD     = 'PASSWORD_MANAGER_ORGANIZATION_NODE_ADD';
    public const PERMISSION_PASSWORD_MANAGER_ORGANIZATION_NODE_REMOVE       = 35;
    public const PERMISSION_NAME_PASSWORD_MANAGER_ORGANIZATION_NODE_REMOVE  = 'PASSWORD_MANAGER_ORGANIZATION_NODE_REMOVE';
    public const PERMISSION_PASSWORD_MANAGER_ORGANIZATION_NODE_UPDATE       = 36;
    public const PERMISSION_PASSWORD_MANAGER_NODE_PWNED_CHART_ALL           = 37;
    public const PERMISSION_NAME_PASSWORD_MANAGER_NODE_PWNED_CHART_ALL      = 'PASSWORD_MANAGER_NODE_PWNED_CHART_ALL';
    public const PERMISSION_LDAP_CONFIG_ACTIVE_GET                          = 39;
    public const PERMISSION_NAME_LDAP_CONFIG_ACTIVE_GET                     = 'LDAP_CONFIG_ACTIVE_GET';
    public const PERMISSION_LDAP_CONFIG_DEACTIVATE                          = 40;
    public const PERMISSION_NAME_LDAP_CONFIG_DEACTIVATE                     = 'LDAP_CONFIG_DEACTIVATE';
    public const PERMISSION_LDAP_CONFIG_REMOVE                              = 41;
    public const PERMISSION_NAME_LDAP_CONFIG_REMOVE                         = 'LDAP_CONFIG_REMOVE';
    public const PERMISSION_PAYMENT_CANCEL                                  = 42;
    public const PERMISSION_NAME_PAYMENT_CANCEL                             = 'PAYMENT_CANCEL';
    public const PERMISSION_CREDENTIAL_ADDITIONAL_DATA_GET                  = 43;
    public const PERMISSION_NAME_CREDENTIAL_ADDITIONAL_DATA_GET             = 'CREDENTIAL_ADDITIONAL_DATA_GET';
    public const PERMISSION_CREDENTIAL_ADDITIONAL_DATA_ADD                  = 44;
    public const PERMISSION_NAME_CREDENTIAL_ADDITIONAL_DATA_ADD             = 'CREDENTIAL_ADDITIONAL_DATA_ADD';
    public const PERMISSION_CREDENTIAL_ADDITIONAL_DATA_GET_VALUE            = 45;
    public const PERMISSION_NAME_CREDENTIAL_ADDITIONAL_DATA_GET_VALUE       = 'CREDENTIAL_ADDITIONAL_DATA_GET_VALUE';
    public const PERMISSION_CREDENTIAL_ADDITIONAL_DATA_DELETE               = 46;
    public const PERMISSION_NAME_CREDENTIAL_ADDITIONAL_DATA_DELETE          = 'CREDENTIAL_ADDITIONAL_DATA_DELETE';
    public const PERMISSION_CREDENTIAL_ACTIVITY_GET                         = 47;
    public const PERMISSION_NAME_CREDENTIAL_ACTIVITY_GET                    = 'CREDENTIAL_ACTIVITY_GET';
    public const PERMISSION_FORGOT_PASSWORD_RESET_PASSWORD                  = 48;
    public const PERMISSION_NAME_FORGOT_PASSWORD_RESET_PASSWORD             = 'FORGOT_PASSWORD_RESET_PASSWORD';
    public const PERMISSION_PASSWORD_MANAGER_NODE_PWNED_ACTIVATE            = 49;
    public const PERMISSION_NAME_PASSWORD_MANAGER_NODE_PWNED_ACTIVATE       = 'PASSWORD_MANAGER_NODE_PWNED_ACTIVATE';
    public const PERMISSION_PASSWORD_MANAGER_NODE_PWNED_IS_ACTIVE           = 50;
    public const PERMISSION_NAME_PASSWORD_MANAGER_NODE_PWNED_IS_ACTIVE      = 'PASSWORD_MANAGER_NODE_PWNED_IS_ACTIVE';
    public const PERMISSION_USERS_EDIT_OTHER_USERS                          = 51;
    public const PERMISSION_NAME_USERS_EDIT_OTHER_USERS                     = 'USERS_EDIT_OTHER_USERS';
    public const PERMISSION_PASSWORD_MANAGER_CREDENTIAL_LIST_ALL            = 52;
    public const PERMISSION_NAME_PASSWORD_MANAGER_CREDENTIAL_LIST_ALL       = 'PASSWORD_MANAGER_CREDENTIAL_LIST_ALL';
    public const PERMISSION_SETTINGS_USER_PROFILE_CONFIGURATION             = 53;
    public const PERMISSION_NAME_SETTINGS_USER_PROFILE_CONFIGURATION        = 'SETTINGS_USER_PROFILE_CONFIGURATION';

}