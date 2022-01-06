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

import {AppStorage} from "./Storage/AppStorage";
import {TemporaryStorage} from "./Storage/TemporaryStorage";
import {Routes as GlobalRoutes} from "./Route/Routes";
import {Host} from "./Backend/Host";
import {Router} from "./Route/Router";
import {DateTimeService} from "./Service/DateTime/DateTimeService";
import {Email} from "./Validation/Email";
import {Phone} from "./Validation/Phone";
import {Axios} from "./Backend/Axios";
import {Website} from "./Validation/Website";
import {UrlService} from "./Service/Http/UrlService";

export const APP_STORAGE = "appstorage.storage";
export const TEMPORARY_STORAGE = "temporarystorage.storage";
export const HOST = "host.backend";
export const ROUTER = "router.router";
export const GLOBAL_ROUTES = "routes.route";
export const DATE_TIME_SERVICE = "datetimeservice.datetime.service";
export const CONFIRMATION_MODAL = "confirmation.modal.ui";
export const EMAIL_VALIDATOR = "validator.email";
export const PHONE_VALIDATOR = "validator.phone";
export const AXIOS = "axios";
export const URL_VALIDATOR = "validator.url";
export const URL_SERVICE = "service.url";

export class StartUp {
    constructor(container) {
        this.container = container;
    }

    setUp() {

        this.container.register(
            APP_STORAGE
            , () => {
                return new AppStorage();
            }
        )

        this.container.register(
            TEMPORARY_STORAGE
            , () => {
                return new TemporaryStorage();
            }
        )

        this.container.register(
            HOST
            , () => {
                return new Host();
            }
        );
        this.container.register(
            ROUTER
            , (container) => {
                return new Router(
                    container.query(HOST)
                );
            }
        );
        this.container.register(
            GLOBAL_ROUTES
            , (container) => {
                return new GlobalRoutes(
                    container.query(HOST)
                );
            }
        );

        this.container.register(
            DATE_TIME_SERVICE
            , (container) => {
                return new DateTimeService(
                    container.query(APP_STORAGE)
                )
            }
        );

        this.container.register(
            EMAIL_VALIDATOR
            , () => {
                return new Email();
            }
        )

        this.container.register(
            PHONE_VALIDATOR
            , () => {
                return new Phone();
            }
        )

        this.container.register(
            AXIOS
            , (container) => {
                return new Axios(container.query(APP_STORAGE))
            }
        )

        this.container.register(
            URL_VALIDATOR
            , () => {
                return new Website();
            }
        )

        this.container.register(
            URL_SERVICE
            , () => {
                return new UrlService();
            }
        );

    }

    getContainer() {
        return this.container;
    }
}
