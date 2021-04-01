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
import {Text} from "./Text";
import {PasswordField} from "../../../../../Common/View/PasswordField";
import {RESPONSE_CODE_OK} from "../../../../../../../../../lib/js/src/Backend/Request";

export class Password extends Text {

    constructor(request, routes) {
        const id = "pwm__login__password";
        super(id);
        this.id = id;
        this.eyeId = "pwm__login__password";
        this.eye = $("#pwm__password__eye");
        this.dataNodeId = "pwm__login__data__password";

        this.request = request;
        this.routes = routes;

    }

    listen() {
        const _this = this;

        this.eye.one(
            "click",
            () => {
                // test

                const passwordField = new PasswordField("#" + _this.id);

                let placeholder = passwordField.getData("credential-placeholder");

                if (false === passwordField.isVisible()) {
                    _this.request.get(
                        _this.routes.getCredential(
                            passwordField.getData("login-id")
                        )
                        , {}
                        , function (response, status, xhr) {
                            const object = JSON.parse(response);
                            const success = object[RESPONSE_CODE_OK];

                            passwordField.setValue(success['decrypted']);
                            passwordField.show();
                            _this.enable();

                        }
                        , function (response, status, xhr) {
                            _this.disable();
                        }
                    );

                } else {
                    passwordField.hide();
                    passwordField.setValue(placeholder);
                    _this.disable();
                }
            }
        )

        super.listen();
    }
}
