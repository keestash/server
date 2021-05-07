/**
 * Keestash
 *
 * Copyright (C) <2020> <Dogan Ucar>
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
import axios from 'axios/index'

export const RESPONSE_CODE_OK = 1000;
export const RESPONSE_CODE_NOT_OK = 2000;
export const RESPONSE_CODE_SESSION_EXPIRED = 3000;

export const RESPONSE_FIELD_MESSAGES = "messages";
export const HEADER_NAME_USER = "x-keestash-user";
export const HEADER_NAME_TOKEN = "x-keestash-token";

export class Axios {

    constructor(appStorage) {
        this.appStorage = appStorage;
    }

    request(url, data = {}) {

        return axios.get(
            url
            , {
                params: data
                , headers: this.getCustomHeaders()
            }
        )
    }

    post(url, data = {}) {

        return axios({
            method: 'post',
            url: url,
            data: data,
            headers: this.getCustomHeaders()
        });
    }

    getCustomHeaders() {
        return {
            [HEADER_NAME_USER]: this.appStorage.getUserHash()
            , [HEADER_NAME_TOKEN]: this.appStorage.getToken()
        };
    }

}
