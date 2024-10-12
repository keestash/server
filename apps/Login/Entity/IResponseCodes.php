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

namespace KSA\Login\Entity;

interface IResponseCodes {

    public const int    RESPONSE_CODE_USER_NOT_FOUND      = 785561;
    public const string RESPONSE_NAME_USER_NOT_FOUND      = 'found.not.user.name.response';
    public const int    RESPONSE_CODE_USER_DISABLED       = 786561;
    public const string RESPONSE_NAME_USER_DISABLED       = 'disabled.user.name.response';
    public const int    RESPONSE_CODE_INVALID_CREDENTIALS = 787561;
    public const string RESPONSE_NAME_INVALID_CREDENTIALS = 'credentials.invalid.name.response';

}
