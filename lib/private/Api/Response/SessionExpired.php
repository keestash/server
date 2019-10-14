<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
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

namespace Keestash\Api\Response;

use KSP\Api\IResponse;
use KSP\L10N\IL10N;

class SessionExpired extends DefaultResponse {

    public function __construct(IL10N $l10n) {
        parent::__construct();
        parent::addMessage(
            IResponse::RESPONSE_CODE_SESSION_EXPIRED
            , [
                "code"      => IResponse::RESPONSE_CODE_SESSION_EXPIRED
                , "message" => $l10n->translate("Your session is expired. Log in again or request new token")
            ]
        );
    }

}