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
import {RESPONSE_CODE_NOT_OK, RESPONSE_CODE_OK} from "../../../../../lib/js/src/Backend/Request";

export class Register {

    constructor(
        request
        , routes
        , longModal
    ) {
        this.request = request;
        this.routes = routes;
        this.longModal = longModal;
        this.registerButtonClicked = false;
        this.registerButton = $("#tl__register__button");
        this.registerForm = $("#register--box");
    }

    setUpClickListener() {
        const _this = this;

        this.registerForm.submit(
            (event) => {
                event.preventDefault();

                if (true === _this.registerButtonClicked) return;
                _this.disableForm();

                const firstName = $("#first_name").val();
                const lastName = $("#last_name").val();
                const userName = $("#user_name").val();
                const password = $("#password").val();
                const phone = $("#phone_prefix").val() + $("#phone").val();
                const email = $("#email").val();
                const website = $("#website").val();
                const passwordRepeat = $("#password_repeat").val();
                const termsAndConditions = $("#terms_and_conditions").is(':checked');

                let values = {
                    'first_name': firstName
                    , 'last_name': lastName
                    , 'user_name': userName
                    , 'email': email
                    , 'phone': phone
                    , 'website': website
                    , 'password': password
                    , 'password_repeat': passwordRepeat
                    , 'terms_and_conditions': termsAndConditions
                };

                _this.request.post(
                    _this.routes.getRegisterAdd()
                    , values
                    , (response) => {
                        let object = JSON.parse(response);
                        let data = null;

                        if (RESPONSE_CODE_OK in object) {
                            data = object[RESPONSE_CODE_OK];
                        } else if (RESPONSE_CODE_NOT_OK in object) {
                            data = object[RESPONSE_CODE_NOT_OK];
                        }

                        _this.longModal.show("Register", "OK", "OK", data['message']);
                        _this.enableForm();
                    }
                    , () => {
                        _this.longModal.show("Register", "OK", "OK", "There was an error during the registration. Please try it again or reach us out!");
                        _this.enableForm();
                    }
                )

            });
    }

    enableForm() {
        this.registerButtonClicked = false;
        this.registerButton.removeClass("disabled");
        $('#register--box input[type="submit"]').prop("disabled", false);
    }

    disableForm() {
        this.registerButtonClicked = true;
        this.registerButton.addClass("disabled");
        $('#register--box input[type="submit"]').prop("disabled", true);
    }

}
