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
use KSA\PasswordManager\ConfigProvider;
use KSP\Core\DTO\RBAC\IPermission;

return [
    CoreConfigProvider::PERMISSION_MAPPING => [
        ConfigProvider::PASSWORD_MANAGER_COMMENT_ADD                  => IPermission::PERMISSION_PASSWORD_MANAGER_NODE_COMMENT_ADD
        , ConfigProvider::PASSWORD_MANAGER_COMMENT_GET_BY_NODE_ID     => IPermission::PERMISSION_PASSWORD_MANAGER_NODE_COMMENT_READ
        , ConfigProvider::PASSWORD_MANAGER_COMMENT_REMOVE             => IPermission::PERMISSION_PASSWORD_MANAGER_NODE_COMMENT_REMOVE
        , ConfigProvider::PASSWORD_MANAGER_NODE_DELETE                => IPermission::PERMISSION_PASSWORD_MANAGER_NODE_DELETE
        , ConfigProvider::PASSWORD_MANAGER_NODE_GET_BY_ID             => IPermission::PERMISSION_PASSWORD_MANAGER_NODE_READ
        , ConfigProvider::PASSWORD_MANAGER_NODE_GET_BY_NAME           => IPermission::PERMISSION_PASSWORD_MANAGER_NODE_READ
        , ConfigProvider::PASSWORD_MANAGER_NODE_MOVE                  => IPermission::PERMISSION_PASSWORD_MANAGER_NODE_MOVE
        , ConfigProvider::PASSWORD_MANAGER_USERS_SHAREABLE            => IPermission::PERMISSION_PASSWORD_MANAGER_NODE_SHAREABLE_USERS
        , ConfigProvider::PASSWORD_MANAGER_ATTACHMENTS_ADD            => IPermission::PERMISSION_PASSWORD_MANAGER_NODE_ATTACHMENT_ADD
        , ConfigProvider::PASSWORD_MANAGER_ATTACHMENTS_GET_BY_NODE_ID => IPermission::PERMISSION_PASSWORD_MANAGER_NODE_ATTACHMENT_READ
        , ConfigProvider::PASSWORD_MANAGER_ATTACHMENTS_REMOVE         => IPermission::PERMISSION_PASSWORD_MANAGER_NODE_ATTACHMENT_REMOVE
        , ConfigProvider::PASSWORD_MANAGER_NODE_UPDATE_AVATAR         => IPermission::PERMISSION_PASSWORD_MANAGER_NODE_AVATAR_UPDATE
        , ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_GET_BY_NODE_ID  => IPermission::PERMISSION_PASSWORD_MANAGER_CREDENTIAL_GET_BY_NODE_ID
        , ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_PASSWORD_UPDATE => IPermission::PERMISSION_PASSWORD_MANAGER_CREDENTIAL_GET_BY_NODE_ID
        , ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_CREATE          => IPermission::PERMISSION_PASSWORD_MANAGER_CREDENTIAL_CREATE
        , ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_UPDATE          => IPermission::PERMISSION_PASSWORD_MANAGER_CREDENTIAL_UPDATE
        , ConfigProvider::PASSWORD_MANAGER_NODE_CREATE                => IPermission::PERMISSION_PASSWORD_MANAGER_NODE_CREATE
        , ConfigProvider::PASSWORD_MANAGER_ORGANIZATION_NODE_ADD      => IPermission::PERMISSION_PASSWORD_MANAGER_ORGANIZATION_NODE_ADD
        , ConfigProvider::PASSWORD_MANAGER_ORGANIZATION_NODE_REMOVE   => IPermission::PERMISSION_PASSWORD_MANAGER_ORGANIZATION_NODE_REMOVE
        , ConfigProvider::PASSWORD_MANAGER_ORGANIZATION_NODE_UPDATE   => IPermission::PERMISSION_PASSWORD_MANAGER_ORGANIZATION_NODE_REMOVE
        , ConfigProvider::PASSWORD_MANAGER_NODE_PWNED_CHART_ALL       => IPermission::PERMISSION_PASSWORD_MANAGER_NODE_PWNED_CHART_ALL
        , ConfigProvider::PASSWORD_MANAGER_NODE_PWNED_CHART_DETAIL    => IPermission::PERMISSION_PASSWORD_MANAGER_NODE_PWNED_CHART_DETAIL
    ]
    , CoreConfigProvider::PERMISSION_FREE  => [
        ConfigProvider::PASSWORD_MANAGER_GENERATE_PASSWORD
        , ConfigProvider::PASSWORD_MANAGER_GENERATE_QUALITY
    ]
];