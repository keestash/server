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
import $ from "jquery";
import {Util} from "../Util/Util";

export const POST = "post";
export const GET = "get";
export const DELETE = "delete";

const DEFAULT_WAIT_TIME_BEFORE_REDIRECT_TO_LOGIN = 5000;

export const RESPONSE_CODE_OK = 1000;
export const RESPONSE_CODE_NOT_OK = 2000;
export const RESPONSE_CODE_SESSION_EXPIRED = 3000;

export class Request {

    constructor(
        appStorage
        , router
    ) {
        this.appStorage = appStorage;
        this.router = router;
    }

    request(route, type, data = {}, successCB, errorCB, preLoad = () => {
    }, contentType = undefined) {

        data['token'] = this.appStorage.getToken();
        data['user_hash'] = this.appStorage.getUserHash();

        $.ajax({
            url: route,
            type: type,
            cache: false,
            data: data,
            timeout: 10000,
            contentType: "application/json",
            dataType: 'json',
            beforeSend: () => {
                preLoad()
            }
            , success: function (response, status, xhr) {
                const object = response;
                if (false === Util.isJson(response)) {
                    console.log("The response got from the server is not JSON");
                    console.log(response);
                    console.log(status);
                    console.log(xhr);
                    return;
                }

                if (RESPONSE_CODE_SESSION_EXPIRED in object) {
                    return false;
                }

                successCB(response, status, xhr);
            },
            error: function (response, status, xhr) {
                errorCB(response, status, xhr);
            }
        });
    };

    post(route, data, successCB, errorCB) {
        if (typeof successCB === "undefined") {
            successCB = this.success;
        }
        if (typeof errorCB === "undefined") {
            errorCB = this.error;
        }

        this.request(
            route
            , POST
            , data
            , successCB
            , errorCB
            , () => {
            }
            , "application/json"
        );
    };

    get(route, data, successCB, errorCB, preLoad = () => {
    }) {

        this.request(
            route
            , GET
            , data
            , successCB
            , errorCB
            , preLoad
        );

    };

    delete(route, data, successCB, errorCB, preLoad = () => {
    }) {

        this.request(
            route
            , DELETE
            , data
            , successCB
            , errorCB
            , preLoad
        );

    };

    handleTokenExpired(jsonObject, route) {
        this.clearCredentials();
        this.redirectToLogin();
    };

    success(x, y, z) {
        console.log(x, y, z);
    };

    error(x, y, z) {
        console.log(x, y, z);
    };

    clearCredentials() {
        this.appStorage.clearAPICredentials();
    };

    redirectToLogin() {
        const _this = this;
        window.setTimeout(
            function () {
                _this.router.routeTo("/login");
            }, DEFAULT_WAIT_TIME_BEFORE_REDIRECT_TO_LOGIN
        );
    };
}
