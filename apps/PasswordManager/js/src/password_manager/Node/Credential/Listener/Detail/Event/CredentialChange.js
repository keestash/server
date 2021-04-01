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

export const EVENT_NAME_CREDENTIAL_CHANGE = "credential_change";
export const EVENT_BIND_ON = "#pwm__detail__part";

export class CredentialChange {

    constructor(request, routes) {
        this.dataNodeId = "pwm__login__data__password";
        this.request = request;
        this.routes = routes;
    }

    init() {
        const _this = this;
        $(EVENT_BIND_ON).on(EVENT_NAME_CREDENTIAL_CHANGE, function () {
            let values = _this.getValues();
            values = _this.normalizeValues(values);

            _this.request.post(
                _this.routes.getPasswordManagerUsersUpdate()
                , values
                , function (x, y, z) {
                    $("#update__message").hide().removeClass("hidden").fadeIn(500);
                }
                , function (x) {
                    console.error(x);
                }
            );
        });
    }

    getValues() {
        const _this = this;

        return {
            "username": $("#pwm__login__username").val()
            , "password": $("#pwm__login__password").val()
            , "url": $("#pwm__login__website").val()
            , "nodeId": $("#" + _this.dataNodeId).attr("data-login-id")
        };

    }

    normalizeValues(values) {

        for (let i = 0; i < values.length; i++) {
            const value = values[i];
            if (typeof value === 'undefined' || value === null || value === "") {
                delete values[i];
            }
        }

        return values;
    }
}
