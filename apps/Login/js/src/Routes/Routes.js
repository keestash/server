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
import {Host} from "../../../../../lib/js/src/Backend/Host";

const LOGIN_SUBMIT = "/login/submit/";
const DEMOUSERS_USER_ADD = "/demousers/user/add/";

export class Routes {
    constructor() {
        this.host = new Host();
    }

    getLoginSubmit() {
        return this.host.getApiHost() + LOGIN_SUBMIT;
    }

    getDemoUsersAdd() {
        return this.host.getApiHost() + DEMOUSERS_USER_ADD;
    }
}
