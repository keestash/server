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
import {PasswordField} from "../Common/View/PasswordField";
import {RESPONSE_CODE_OK} from "../../../../../lib/js/src/Backend/Request";

export class PublicShare {

    constructor(request, miniModal, routes) {
        this.request = request;
        this.miniModal = miniModal;
        this.routes = routes;
    }

    init() {
        const _this = this;
        const eye = $("#pwm__password__eye");
        const passwordInput = $("#pwm__login__password");

        $(eye).click(
            () => {

                const passwordField = new PasswordField("#pwm__login__password");
                const placeholder = passwordInput.attr("placeholder");

                if (false === passwordField.isVisible()) {

                    _this.request.get(
                        _this.routes.getPublicShareDecrypt(
                            passwordField.getData("node-hash")
                        )
                        , {}
                        , (response) => {
                            const object = JSON.parse(response);
                            const success = object[RESPONSE_CODE_OK];

                            passwordField.setValue(success['messages']['decrypted']);
                            passwordField.show();

                        }
                        , (response) => {
                            _this.miniModal.show("Error while retrieving credentials");
                        }
                    );

                } else {
                    passwordField.hide();
                    passwordField.setValue(placeholder);
                }

            });
    }
}

