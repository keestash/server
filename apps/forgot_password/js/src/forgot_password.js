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
import '../../../../lib/js/src/semantic/semantic.min';
import Formula from "../../../../lib/js/src/Formula";
import Input from "../../../../lib/js/src/UI/Input";
import modal from "../../../../lib/js/src/UI/modal";

(function () {

    if (!Keestash.ForgotPassword) {
        Keestash.ForgotPassword = {};
    }
    Keestash.ForgotPassword = {
        buttonClicked: false,
        SELECTORS: {
            FORGOT_PASSWORD_FORM: "#tl__forgot__password"
            , USERNAME_OR_PASSWORD: "#tl__forgot__password__username__or__email"
            , SUBMIT_BUTTON: "#tl__login__button"
        },
        enableForm: function () {
            this.buttonClicked = false;
            $(this.SELECTORS).removeClass("disable");
        },
        disableForm: function () {
            this.buttonClicked = true;
            $(this.SELECTORS).addClass("disable");
        },
        init: function () {
            let formula = new Formula();
            let that = this;

            that.buttonClicked = false;
            $(that.SELECTORS.FORGOT_PASSWORD_FORM).submit(
                function (event) {
                    event.preventDefault();

                    let username_or_email = $(that.SELECTORS.USERNAME_OR_PASSWORD).val();

                    if (true === that.buttonClicked) return;
                    that.disableForm();

                    if (username_or_email === "") {
                        that.enableForm();
                        Input.invalid(that.SELECTORS.USERNAME_OR_PASSWORD);
                        return false;
                    }

                    formula.post(
                        Keestash.Main.getApiHost() + "/forgot_password/submit/"
                        , {'username_or_email': username_or_email}
                        , function (response, status, xhr) {
                            let object = JSON.parse(response);
                            let result_object = null;
                            // TODO get the response from server!!
                            let message = "There was an error. Please try again or contact our support";
                            if (1000 in object) {
                                result_object = object[1000];
                                that.enableForm();
                            } else if (2000 in object) {
                                result_object = object[2000];
                                // 2000 means error!
                                that.disableForm();
                            }

                            if (null !== result_object) {
                                message = result_object['message'];
                            }
                            modal.miniModal(message);
                        }
                        , function (response, status, xhr) {
                            // TODO get the response from server!!
                            modal.miniModal("There was an error. Please try again or contact our support");
                            that.enableForm();
                        }
                    );
                }
            );
        }
    }
})();
$(document).ready(function () {
    Keestash.ForgotPassword.init();
});