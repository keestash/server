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
import Formula from "../../../../lib/js/src/Formula";
import System from "../../../../lib/js/src/System";
import Email from "../../../../lib/js/src/Validation/Email";
import modal from "../../../../lib/js/src/UI/modal";

(function () {
    if (!Keestash.Register) {
        Keestash.Register = {};
    }

    Keestash.Register = {
        registerButtonClicked: false,
        enabler: {
            FIRST_NAME: false
            , LAST_NAME: false
            , USER_NAME: false
            , PASSWORD: false
            , EMAIL: false
            , PASSWORD_REPEAT: false
        },
        SELECTORS: {
            FIRST_NAME: "#tl__register__first__name"
            , LAST_NAME: "#tl__register__last__name"
            , USER_NAME: "#tl__register__user__name"
            , PASSWORD: "#tl__register__password"
            , PASSWORD_INVALID_HINT: "#tl__register__password__invalid__text"
            , EMAIL_INVALID_HINT: "#tl__register__email__invalid__text"
            , PASSWORD_REPEAT_INVALID_HINT: "#tl__register__password__repeat__invalid__text"
            , EMAIL_TAKEN_INVALID_HINT: "#tl__register__email__taken__text"
            , PASSWORD_REPEAT: "#tl__register__password__repeat"
            , EMAIL: "#tl__register__email"
            , TERMS_AND_CONDITIONS: "#tl__register__terms__and__conditions"
            , REGISTER_BUTTON: "#tl__register__button"
        },
        enableForm: function () {
            this.registerButtonClicked = false;
            $(this.SELECTORS.REGISTER_BUTTON).removeClass("disabled");
        },
        disableForm: function () {
            this.registerButtonClicked = true;
            $(this.SELECTORS.REGISTER_BUTTON).addClass("disabled");
        },
        init: function () {
            let that = this;

            that.setUpOnChange();
            $("#tl__register__terms__and__conditions").change(function () {
                that.changeButtonState($(this).is(':checked'), that.SELECTORS);
            });

            $(that.SELECTORS.REGISTER_BUTTON).click(function (event) {
                event.preventDefault();

                if (true === that.registerButtonClicked) return;
                // that.disableForm();

                let firstName = $(that.SELECTORS.FIRST_NAME).val();
                let lastName = $(that.SELECTORS.LAST_NAME).val();
                let userName = $(that.SELECTORS.USER_NAME).val();
                let email = $(that.SELECTORS.EMAIL).val();
                let password = $(that.SELECTORS.PASSWORD).val();
                let passwordRepeat = $(that.SELECTORS.PASSWORD_REPEAT).val();
                let termsAndConditions = $(that.SELECTORS.TERMS_AND_CONDITIONS).is(':checked');
                let formula = new Formula();

                let values = {
                    'first_name': firstName
                    , 'last_name': lastName
                    , 'user_name': userName
                    , 'email': email
                    , 'password': password
                    , 'password_repeat': passwordRepeat
                    , 'terms_and_conditions': termsAndConditions
                };

                formula.post(
                    Keestash.Main.getApiHost() + "/register/add/"
                    , values
                    , function (response, status, xhr) {
                        let obj = JSON.parse(response);
                        let json_object = null;

                        if (1000 in obj) {
                            json_object = obj[1000];
                        } else if (2000 in obj) {
                            json_object = obj[2000];
                            // 2000 means error!
                            // that.disableForm();
                        }
                        modal.miniModal(json_object['message']);
                        // that.enableForm();
                    }
                    , function (response, status, xhr) {
                        modal.miniModal("There was an error during the registration. Please try it again or reach us out!");
                        // that.disableForm();
                    }
                );

            });
        },
        setUpOnChange: function () {
            let formula = new Formula();
            let emailValidation = new Email();

            let that = this;
            $(that.SELECTORS.PASSWORD).keyup(function () {
                let thiz = $(this);
                let value = thiz.val();
                let valLength = value.length;

                if (valLength < 8) {
                    that.changeState(true, that.SELECTORS.PASSWORD_INVALID_HINT);
                    that.enabler.PASSWORD = false;
                    that.checkIfEnabled_2();
                    return;
                }

                formula.post(
                    Keestash.Main.getApiHost() + "/password_requirements/"
                    , {'password': value}
                    , function (response, status, xhr) {
                        let obj = JSON.parse(response);

                        let validPazzword = false;

                        if (1000 in obj) {
                            validPazzword = true;
                        } else if (2000 in obj) {
                            validPazzword = false;
                        }

                        let stateChanged = false === validPazzword && value !== "";

                        that.changeState(stateChanged, that.SELECTORS.PASSWORD_INVALID_HINT);
                        that.enabler.PASSWORD = true === validPazzword && value !== "";
                        that.checkIfEnabled_2();
                    }
                    , function () {
                        modal.miniModal("Error. Please try again");
                    }
                )

            });

            $(that.SELECTORS.PASSWORD_REPEAT).keyup(function () {
                let thiz = $(this);
                let value = thiz.val();
                let password = $(that.SELECTORS.PASSWORD).val();
                let stateChanged = value !== password && value !== "";
                that.changeState(stateChanged, that.SELECTORS.PASSWORD_REPEAT_INVALID_HINT);
                that.enabler.PASSWORD_REPEAT = value === password && value !== "";
                that.checkIfEnabled_2();
            });

            $(that.SELECTORS.FIRST_NAME).keyup(function () {
                let thiz = $(this);
                let value = thiz.val();
                that.enabler.FIRST_NAME = value !== "";
                that.checkIfEnabled_2();
            });

            $(that.SELECTORS.LAST_NAME).keyup(function () {
                let thiz = $(this);
                let value = thiz.val();
                that.enabler.LAST_NAME = value !== "";
                that.checkIfEnabled_2();
            });

            $(that.SELECTORS.EMAIL).keyup(function () {
                let thiz = $(this);
                let value = thiz.val();
                let validEmail = emailValidation.isValid(value);
                let stateChanged = false === validEmail && value !== "";

                that.changeState(stateChanged, that.SELECTORS.EMAIL_INVALID_HINT);

                that.enabler.EMAIL = true === validEmail && value !== "";
                that.checkIfEnabled_2();
            });

            $(that.SELECTORS.USER_NAME).keyup(function () {
                let thiz = $(this);
                let value = thiz.val();
                that.enabler.USER_NAME = value !== "";
                that.checkIfEnabled_2();
            });

        },
        checkIfEnabled_2: function () {
            var that = this;
            var system = new System();
            system.throttle(function () {
                that.checkIfEnabled()
            }, 500)()
        },
        checkIfEnabled: function () {
            let enabled = true;
            let element = $("#tl__register__terms__and__conditions");
            $.each(this.enabler, function (i, v) {
                enabled = v && enabled;
            });

            if (enabled === true) {
                element.removeAttr("disabled");
            } else {
                element.prop("checked", false);
                element.removeAttr("checked");
                element.attr("disabled", true);
                this.changeButtonState(false)
            }
        },
        changeState: function (show, selectorName) {
            let element = $(selectorName);

            if (true === show) {
                element.fadeIn(500, function () {
                    $(this).show();
                });
            } else {
                element.fadeOut(500, function () {
                    $(this).hide();
                });
            }
        },
        changeButtonState: function (enable) {
            let button = $(this.SELECTORS.REGISTER_BUTTON);

            button.addClass("disabled");
            if (true === enable) {
                button.removeClass("disabled");
            } else {

            }
        }
    }
})();
$(document).ready(function () {
    Keestash.Register.init();
});
