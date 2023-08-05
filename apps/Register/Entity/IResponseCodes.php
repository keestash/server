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

namespace KSA\Register\Entity;

class IResponseCodes {

    public const RESPONSE_CODE_RESET_PASSWORD_INVALID_INPUT                   = 2071896;
    public const RESPONSE_NAME_RESET_PASSWORD_INVALID_INPUT                   = 'input.invalid.password.reset.name.response';
    public const RESPONSE_CODE_RESET_PASSWORD_USER_NOT_FOUND                  = 1763755;
    public const RESPONSE_NAME_RESET_PASSWORD_USER_NOT_FOUND                  = 'found.not.user.password.reset.name.response';
    public const RESPONSE_CODE_RESET_PASSWORD_USER_DISABLED                   = 7395405;
    public const RESPONSE_NAME_RESET_PASSWORD_USER_DISABLED                   = 'disabled.user.password.reset.name.response';
    public const RESPONSE_CODE_RESET_PASSWORD_RESET_MAIL_SENT                 = 8426136;
    public const RESPONSE_NAME_RESET_PASSWORD_RESET_MAIL_SENT                 = 'sent.mail.reset.password.reset.name.response';
    public const RESPONSE_CODE_RESET_PASSWORD_RESET_MAIL_ALREADY_SENT         = 9903356;
    public const RESPONSE_NAME_RESET_PASSWORD_RESET_MAIL_ALREADY_SENT         = 'sent.already.mail.reset.password.reset.name.response';
    public const RESPONSE_CODE_RESET_PASSWORD_RETRIEVE_USER_BY_HASH_NOT_FOUND = 133909;
    public const RESPONSE_NAME_RESET_PASSWORD_RETRIEVE_USER_BY_HASH_NOT_FOUND = 'found.not.hash.by.user.retrieve.password.reset.name.response';
    public const RESPONSE_CODE_RESET_PASSWORD_RETRIEVE_USER_BY_HASH_EXPIRED   = 133809;
    public const RESPONSE_NAME_RESET_PASSWORD_RETRIEVE_USER_BY_HASH_EXPIRED   = 'expired.hash.by.user.retrieve.password.reset.name.response';
    public const RESPONSE_CODE_RESET_PASSWORD_CONFIRM_USER_BY_HASH_NOT_FOUND  = 133349;
    public const RESPONSE_NAME_RESET_PASSWORD_CONFIRM_USER_BY_HASH_NOT_FOUND  = 'found.not.hash.by.user.confirm.password.reset.name.response';
    public const RESPONSE_CODE_RESET_PASSWORD_CONFIRM_INVALID_PASSWORD  = 3422341;
    public const RESPONSE_NAME_RESET_PASSWORD_CONFIRM_INVALID_PASSWORD  = 'password.invalid.confirm.password.reset.name.response';

}