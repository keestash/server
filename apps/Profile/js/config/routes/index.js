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

const SETTINGS_USER_GET = '/users/get/{userHash}/';
const SETTINGS_USER_EDIT = '/users/edit/';

const host = new Host();

export const ROUTES = {

    getUsersGet(userHash) {
        let route = SETTINGS_USER_GET;
        route = route.replace("{userHash}", userHash);
        return host.getApiHost() + route;
    },

    getAssetUrl(jsonWebToken) {
        return host.getAssetUrl() + '?token=' + jsonWebToken;
    },

    getUpdateUser() {
        return host.getApiHost() + SETTINGS_USER_EDIT;

    },

}
