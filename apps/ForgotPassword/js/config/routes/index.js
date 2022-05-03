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
import {Host} from "../../../../../lib/js/src/Backend/Host";

const host = new Host();

const LOGIN_APP_CONFIGURATION = '/forgot_password/configuration/';
const FORGOT_PASSWORD_SUBMIT = "/forgot_password/submit";
const RESET_PASSWORD_ACCOUNT_DETAILS = "/reset_password/account_details/{token}/";
const RESET_PASSWORD = "/reset_password/update/";

export const ROUTES = {

    getConfiguration() {
        return host.getApiHost() + LOGIN_APP_CONFIGURATION;
    }

    , getForgotPasswordSubmit() {
        return host.getApiHost() + FORGOT_PASSWORD_SUBMIT;
    }

    /**
     *
     * @param {String} token
     * @returns {string}
     */
    , getAccountDetails(token) {
        let route = RESET_PASSWORD_ACCOUNT_DETAILS.replace("{token}", "" + token);
        return host.getApiHost() + route;
    }

    , getResetPasswordSubmit() {
        return host.getApiHost() + RESET_PASSWORD;
    }
}
