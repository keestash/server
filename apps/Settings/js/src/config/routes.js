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

const host = new Host();

const ORGANIZATION_LIST = "/organizations/all/";
const ORGANIZATION_ADD = "/organizations/add/";
const ORGANIZATION_ACTIVATE = "/organizations/activate/";
const ORGANIZATION_DETAILS = "/organizations/{id}/";

const PASSWORD_REQUIREMENTS = "/password_requirements/";

const USER_GET = "/user/exists/{userName}/";
const USERS_ALL = "/users/all/";
const USERS_EDIT = "/users/edit/";

const EMAIL_GET = "/user/mail/exists/{address}/";
const REGISTER_ADD = "/register/add/";

export const ROUTES = {
    GET_ORGANIZATION_LIST: () => {
        return host.getApiHost() + ORGANIZATION_LIST;
    },
    GET_ORGANIZATION_ADD: () => {
        return host.getApiHost() + ORGANIZATION_ADD;
    },
    GET_ALL_USERS: () => {
        return host.getApiHost() + USERS_ALL;
    },
    GET_ORGANIZATION_ACTIVATE: () => {
        return host.getApiHost() + ORGANIZATION_ACTIVATE;
    },
    GET_ORGANIZATION_DETAILS: (organization) => {
        return host.getHost() + ORGANIZATION_DETAILS.replace('{id}', organization.id);
    },
    PASSWORD_REQUIREMENTS: () => {
        return host.getApiHost() + PASSWORD_REQUIREMENTS
    },
    USER_EXISTS: (username) => {
        const route = USER_GET.replace("{userName}", username);
        return host.getApiHost() + route;
    },
    MAIL_EXISTS: (emailAddress) => {
        const route = EMAIL_GET.replace("{address}", emailAddress);
        return host.getApiHost() + route;
    },
    REGISTER_ADD: () => {
        return host.getApiHost() + REGISTER_ADD;
    },
    USERS_ALL: () => {
        return host.getApiHost() + USERS_ALL;
    },
    USERS_EDIT: () => {
        return host.getApiHost() + USERS_EDIT;
    },
}