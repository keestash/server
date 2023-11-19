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
    public const RESPONSE_CODE_RESET_PASSWORD_CONFIRM_INVALID_PASSWORD        = 3422341;
    public const RESPONSE_NAME_RESET_PASSWORD_CONFIRM_INVALID_PASSWORD        = 'password.invalid.confirm.password.reset.name.response';
    public const RESPONSE_CODE_TERMS_AND_CONDITIONS_NOT_AGREED                = 198378;
    public const RESPONSE_NAME_TERMS_AND_CONDITIONS_NOT_AGREED                = 'agreed.not.conditions.and.terms.response';
    public const RESPONSE_CODE_INVALID_PASSWORD                               = 45612365;
    public const RESPONSE_NAME_INVALID_PASSWORD                               = 'password.invalid';
    public const RESPONSE_CODE_VALIDATE_USER                                  = 5672344675;
    public const RESPONSE_NAME_VALIDATE_USER                                  = 'user.validate';
    public const RESPONSE_CODE_ERROR_CREATING_USER                            = 67823415;
    public const RESPONSE_NAME_ERROR_CREATING_USER                            = 'user.creating.error';
    public const RESPONSE_CODE_USER_CREATED                                   = 67891234;
    public const RESPONSE_NAME_USER_CREATED                                   = 'user.created';
    public const RESPONSE_CODE_USER_SUBSCRIPTION_CREATED                      = 23491234345;
    public const RESPONSE_NAME_USER_SUBSCRIPTION_CREATED                      = 'created.subscription.user';
    public const RESPONSE_CODE_REGISTER_DISABLED                              = 567532;
    public const RESPONSE_NAME_REGISTER_DISABLED                              = 'disabled.register';

}