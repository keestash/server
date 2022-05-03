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
import {Host} from "../../../../../lib/js/src/Backend/Host";

const ROUTE_NAME_APPS_GET_ALL = '/apps/get/all/';
const ROUTE_NAME_APPS_UPDATE  = '/apps/update/';

const host = new Host();

export const ROUTES = {
    getAppsAll: () => {
        return host.getApiHost() + ROUTE_NAME_APPS_GET_ALL;
    },

    getUpdateApps(){
        return host.getApiHost() + ROUTE_NAME_APPS_UPDATE;
    }

}
