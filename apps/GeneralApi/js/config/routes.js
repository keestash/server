/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

import {Host} from "../../../../lib/js/src/Backend/Host";

const host = new Host();

const ORGANIZATION_GET = "/organizations/{id}/";
const ORGANIZATION_UPDATE = "/organizations/update/";
const ORGANIZATION_USER_CHANGE = "/organizations/user/change/";

export const MODE_ADD = 'add.mode';
export const MODE_REMOVE = 'remove.mode';

export const ROUTES = {
    GET_ORGANIZATION_GET: (id) => {
        return host.getApiHost() + ORGANIZATION_GET.replace('{id}', id);
    },
    GET_ORGANIZATION_UPDATE: () => {
        return host.getApiHost() + ORGANIZATION_UPDATE;
    },
    GET_ORGANIZATION_USER_CHANGE: () => {
        return host.getApiHost() + ORGANIZATION_USER_CHANGE;
    },
}