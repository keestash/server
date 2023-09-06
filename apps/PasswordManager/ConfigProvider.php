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

namespace KSA\PasswordManager;


final class ConfigProvider {

    public const PASSWORD_MANAGER_ORGANIZATION_NODE_ADD    = "/password_manager/organization/node/add[/]";
    public const PASSWORD_MANAGER_ORGANIZATION_NODE_UPDATE = "/password_manager/organization/node/update[/]";
    public const PASSWORD_MANAGER_ORGANIZATION_NODE_REMOVE = "/password_manager/organization/node/remove/";
    public const PASSWORD_MANAGER_PUBLIC_SHARE_DECRYPT     = "/password_manager/public_share/decrypt/:hash[/]";
    public const PASSWORD_MANAGER_PUBLIC_SHARE             = '/password_manager/share[/]';
    public const PASSWORD_MANAGER_PUBLIC_SHARE_PUBLIC      = "/password_manager/share/public[/]";
    public const PASSWORD_MANAGER_PUBLIC_SHARE_REMOVE      = '/password_manager/share/remove[/]';

    public const PASSWORD_MANAGER_NODE_DELETE                          = '/password_manager/node/delete';
    public const PASSWORD_MANAGER_CREDENTIAL_PASSWORD_UPDATE           = "/password_manager/credential/password/update/";
    public const PASSWORD_MANAGER_CREDENTIAL_CREATE                    = '/password_manager/node/credential/create';
    public const PASSWORD_MANAGER_CREDENTIAL_UPDATE                    = '/password_manager/credential/update[/]';
    public const PASSWORD_MANAGER_CREDENTIAL_GET_BY_NODE_ID            = '/password_manager/credential/get/:node_id[/]';
    public const PASSWORD_MANAGER_CREDENTIAL_ADDITIONAL_DATA_GET       = '/password_manager/credential/additional_data/get/:credentialId';
    public const PASSWORD_MANAGER_CREDENTIAL_ADDITIONAL_DATA_GET_VALUE = '/password_manager/credential/additional_data/get/value/:advid';
    public const PASSWORD_MANAGER_CREDENTIAL_ADDITIONAL_DATA_ADD       = '/password_manager/credential/additional_data/add';
    public const PASSWORD_MANAGER_CREDENTIAL_ADDITIONAL_DATA_DELETE    = '/password_manager/credential/additional_data/delete/:advid';
    public const PASSWORD_MANAGER_CREDENTIAL_ACTIVITY_GET              = '/password_manager/credential/additional_data/get/:referenceKey/:appId';
    public const PASSWORD_MANAGER_CREDENTIAL_LIST_ALL                  = '/password_manager/credential/list_all';

    public const PASSWORD_MANAGER_NODE_PWNED_CHART_ALL    = '/password_manager/node/pwned/chart/all';
    public const PASSWORD_MANAGER_NODE_PWNED_CHANGE_STATE = '/password_manager/node/pwned/change_state';
    public const PASSWORD_MANAGER_NODE_PWNED_IS_ACTIVE    = '/password_manager/node/pwned/is_active';

    public const PASSWORD_MANAGER_ATTACHMENTS_ADD            = '/password_manager/attachments/add';
    public const PASSWORD_MANAGER_ATTACHMENTS_GET_BY_NODE_ID = '/password_manager/attachments/get/:nodeId';
    public const PASSWORD_MANAGER_ATTACHMENTS_REMOVE         = '/password_manager/attachments/remove[/]';
    public const PASSWORD_MANAGER_ATTACHMENTS_DOWNLOAD       = '/password_manager/attachments/download/:fileId/:jwt';

    public const PASSWORD_MANAGER_GENERATE_QUALITY = "/password_manager/generate/quality/:value";

    public const PASSWORD_MANAGER_COMMENT_ADD            = '/password_manager/comment/add[/]';
    public const PASSWORD_MANAGER_COMMENT_GET_BY_NODE_ID = '/password_manager/comment/get/:nodeId/[:sortField/][:sortDirection/]';
    public const PASSWORD_MANAGER_COMMENT_REMOVE         = '/password_manager/comment/remove[/]';

    public const PASSWORD_MANAGER_GENERATE_PASSWORD = '/password_manager/generate_password/:length/:upperCase/:lowerCase/:digit/:specialChars[/]';

    public const PASSWORD_MANAGER_NODE_GET_BY_ID     = '/password_manager/node/get/:node_id';
    public const PASSWORD_MANAGER_NODE_GET_BY_NAME   = '/password_manager/node/name/:name';
    public const PASSWORD_MANAGER_NODE_MOVE          = '/password_manager/node/move';
    public const PASSWORD_MANAGER_NODE_UPDATE        = '/password_manager/node/folder/update';
    public const PASSWORD_MANAGER_NODE_UPDATE_AVATAR = '/password_manager/node/update/avatar[/]';

    public const PASSWORD_MANAGER_USERS_SHAREABLE = '/password_manager/users/shareable/:nodeId/:query/';

    public const PASSWORD_MANAGER_NODE_CREATE           = '/password_manager/node/folder/create';
    public const PASSWORD_MANAGER_SEARCH                = '/password_manager/search/:search';
    public const PASSWORD_MANAGER_FOLDER_CREATE_BY_PATH = '/password_manager/node/folder/create/path';

    public const APP_ID                         = 'passwordManager';
    public const FILE_UPLOAD_ALLOWED_EXTENSIONS = 'extensions.allowed.upload.file';

    public function __invoke(): array {
        return require __DIR__ . '/config/config.php';
    }

}