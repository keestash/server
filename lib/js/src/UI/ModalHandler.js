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
import '../semantic/semantic.min';
import modal from "./modal";

export const RESPONSE_CODE_OK = 1000;
export const RESPONSE_CODE_NOT_OK = 2000;
export const RESPONSE_CODE_SESSION_EXPIRED = 3000;

export default {

    handleSuccess: function (response) {
        let object = JSON.parse(response);
        let message = null;
        let success = false;

        if (RESPONSE_CODE_OK in object) {
            message = object[RESPONSE_CODE_OK]["message"];
            success = true;
        } else {
            message = object[RESPONSE_CODE_NOT_OK]["message"];
        }

        modal.miniModal(message);
        return success;
    }
    , handleError(response) {
        modal.miniModal("Error " + response);
    }
}