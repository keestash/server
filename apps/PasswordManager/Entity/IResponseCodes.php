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

    public const RESPONSE_CODE_INVALID_NODE_ID           = 957586;
    public const RESPONSE_NAME_INVALID_NODE_ID           = 'id.node.invalid.code.response';
    public const RESPONSE_CODE_NO_QUALITY_VALUE_PROVIDED = 879599;
    public const RESPONSE_NAME_NO_QUALITY_VALUE_PROVIDED = 'provided.value.quality.no';
    public const RESPONSE_CODE_PARENT_NODE_NOT_FOUND     = 385262;
    public const RESPONSE_NAME_PARENT_NODE_NOT_FOUND     = 'found.not.node.parent';
    public const RESPONSE_CODE_INVALID_FOLDER_NAME       = 805934;
    public const RESPONSE_NAME_INVALID_FOLDER_NAME       = 'name.folder.invalid';
    public const RESPONSE_CODE_INVALID_FOLDER_DELIMITER  = 343454;
    public const RESPONSE_NAME_INVALID_FOLDER_DELIMITER  = 'delimiter.folder.invalid';

}