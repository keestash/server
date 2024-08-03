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

namespace KSA\PasswordManager\Entity;

interface IResponseCodes {

    public const int    RESPONSE_CODE_INVALID_NODE_ID                   = 957586;
    public const string RESPONSE_NAME_INVALID_NODE_ID                   = 'id.node.invalid.code.response';
    public const int    RESPONSE_CODE_NO_QUALITY_VALUE_PROVIDED         = 879599;
    public const string RESPONSE_NAME_NO_QUALITY_VALUE_PROVIDED         = 'provided.value.quality.no';
    public const int    RESPONSE_CODE_PARENT_NODE_NOT_FOUND             = 385262;
    public const string RESPONSE_NAME_PARENT_NODE_NOT_FOUND             = 'found.not.node.parent';
    public const int    RESPONSE_CODE_NODE_NOT_FOUND                    = 442935;
    public const string RESPONSE_NAME_NODE_NOT_FOUND                    = 'found.not.node';
    public const int    RESPONSE_CODE_INVALID_FOLDER_NAME               = 805934;
    public const string RESPONSE_NAME_INVALID_FOLDER_NAME               = 'name.folder.invalid';
    public const int    RESPONSE_CODE_INVALID_FOLDER_DELIMITER          = 343454;
    public const string RESPONSE_NAME_INVALID_FOLDER_DELIMITER          = 'delimiter.folder.invalid';
    public const int    RESPONSE_CODE_NO_FILES_GIVEN                    = 143377;
    public const string RESPONSE_NAME_NO_FILES_GIVEN                    = 'given.files.no';
    public const int    RESPONSE_CODE_NODE_ACCESS_UNAUTHORIZED          = 678534;
    public const string RESPONSE_NAME_NODE_ACCESS_UNAUTHORIZED          = 'unauthorized.access.node';
    public const int    RESPONSE_CODE_NODE_ATTACHMENT_ADD_NO_NODE_FOUND = 891256;
    public const string RESPONSE_NAME_NODE_ATTACHMENT_ADD_NO_NODE_FOUND = 'found.no.node.add.attachment.node';
    public const int    RESPONSE_CODE_NODE_SHARE_PUBLIC_INVALID_PAYLOAD = 7902345;
    public const string RESPONSE_NAME_NODE_SHARE_PUBLIC_INVALID_PAYLOAD = 'payload.invalid.public.share.node';
    public const int    RESPONSE_CODE_NODE_SHARE_PUBLIC_NOT_FOUND       = 5672487;
    public const string RESPONSE_NAME_NODE_SHARE_PUBLIC_NOT_FOUND       = 'found.not.public.share.node';
    public const int    RESPONSE_CODE_NODE_SHARE_PUBLIC_NO_SHARE_EXISTS = 7893576;
    public const string RESPONSE_NAME_NODE_SHARE_PUBLIC_NO_SHARE_EXISTS = 'exists.share.no.public.share.node';
}
