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
import {Host} from "./Backend/Host";
import {Router} from "./Route/Router";
import {DateTimeService} from "./Service/DateTime/DateTimeService";
import {Axios} from "./Backend/Axios";
import {UrlService} from "./Service/Http/UrlService";
import {PhoneService} from "./Service/Phone/PhoneService";
import {EmailService} from "./Service/Email/EmailService";

export const APP_STORAGE = "appstorage.storage";
export const TEMPORARY_STORAGE = "temporarystorage.storage";
export const HOST = "host.backend";
export const ROUTER = "router.router";
export const DATE_TIME_SERVICE = "datetimeservice.datetime.service";
export const CONFIRMATION_MODAL = "confirmation.modal.ui";
export const AXIOS = "axios";
export const URL_SERVICE = "service.url";
export const PHONE_SERVICE = "service.phone";
export const EMAIL_SERVICE = "service.email";

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
            PHONE_SERVICE
            , () => {
                return new PhoneService();
            }
        )

        this.container.register(
            EMAIL_SERVICE
            , () => {
                return new EmailService();
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
            DATE_TIME_SERVICE
            , (container) => {
                return new DateTimeService(
                    container.query(APP_STORAGE)
                )
            }
        );

        this.container.register(
            AXIOS
            , (container) => {
                return new Axios(container.query(APP_STORAGE));
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
