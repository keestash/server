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

use KSA\Register\Entity\IResponseCodes;

return [
    IResponseCodes::RESPONSE_NAME_RESET_PASSWORD_INVALID_INPUT                     => IResponseCodes::RESPONSE_CODE_RESET_PASSWORD_INVALID_INPUT
    , IResponseCodes::RESPONSE_NAME_RESET_PASSWORD_USER_NOT_FOUND                  => IResponseCodes::RESPONSE_CODE_RESET_PASSWORD_USER_NOT_FOUND
    , IResponseCodes::RESPONSE_NAME_RESET_PASSWORD_USER_DISABLED                   => IResponseCodes::RESPONSE_CODE_RESET_PASSWORD_USER_DISABLED
    , IResponseCodes::RESPONSE_NAME_RESET_PASSWORD_RESET_MAIL_SENT                 => IResponseCodes::RESPONSE_CODE_RESET_PASSWORD_RESET_MAIL_SENT
    , IResponseCodes::RESPONSE_NAME_RESET_PASSWORD_RESET_MAIL_ALREADY_SENT         => IResponseCodes::RESPONSE_CODE_RESET_PASSWORD_RESET_MAIL_ALREADY_SENT
    , IResponseCodes::RESPONSE_NAME_RESET_PASSWORD_RETRIEVE_USER_BY_HASH_NOT_FOUND => IResponseCodes::RESPONSE_CODE_RESET_PASSWORD_RETRIEVE_USER_BY_HASH_NOT_FOUND
    , IResponseCodes::RESPONSE_NAME_RESET_PASSWORD_RETRIEVE_USER_BY_HASH_EXPIRED   => IResponseCodes::RESPONSE_CODE_RESET_PASSWORD_RETRIEVE_USER_BY_HASH_EXPIRED
    , IResponseCodes::RESPONSE_NAME_RESET_PASSWORD_CONFIRM_USER_BY_HASH_NOT_FOUND  => IResponseCodes::RESPONSE_CODE_RESET_PASSWORD_CONFIRM_USER_BY_HASH_NOT_FOUND
    , IResponseCodes::RESPONSE_NAME_RESET_PASSWORD_CONFIRM_INVALID_PASSWORD        => IResponseCodes::RESPONSE_CODE_RESET_PASSWORD_CONFIRM_INVALID_PASSWORD
];