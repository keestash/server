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
import $ from 'jquery';
import {RESPONSE_CODE_SESSION_EXPIRED} from "./UI/ModalHandler";
import modal from "./UI/modal";
import {Router} from "./Route/Router";
import {AppStorage} from "./Storage/AppStorage";

export default function Formula() {
    const POST = "post";
    const GET = "get";

    this.request = function (route, type, data, successCB, errorCB) {

        let appStorage = new AppStorage();
        data['token'] = appStorage.getToken();
        data['user_hash'] = appStorage.getUserHash();

        $.ajax({
            url: route,
            type: type,
            cache: false,
            data: data,
            success: function (response, status, xhr) {

                const object = JSON.parse(response);

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

    this.post = function (route, data, successCB, errorCB) {
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
        );
    };

    this.get = function (route, data, successCB, errorCB) {

        this.request(
            route
            , GET
            , data
            , successCB
            , errorCB
        );

    };

    this.handleTokenExpired = function (jsonObject, route) {
        this.clearCredentials();
        this.notifyExpiry(jsonObject, route);
        this.redirectToLogin();
    };

    this.notifyExpiry = function (jsonObject, route) {
        modal.miniModal(jsonObject[RESPONSE_CODE_SESSION_EXPIRED]['message'] + " for route " + route);
    };

    this.success = function (x, y, z) {
        x = JSON.parse(x);
        modal.miniModal(x[1000]["message"]);
    };

    this.error = function (x, y, z) {
        x = JSON.parse(x);
        modal.miniModal(x[2000]["message"]);
    };

    this.clearCredentials = function () {
        let appStorage = new AppStorage();
        appStorage.clearAPICredentials();
    };

    this.redirectToLogin = function () {
        window.setTimeout(
            function () {
                const router = new Router(
                    Keestash.Main.getHost()
                );

                router.routeTo("/login");
            }, 5000
        );
    };
};