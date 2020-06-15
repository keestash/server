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
import modal from "../../../../lib/js/src/UI/modal";
import {Register} from "./RegisterForm/Register";
import {Request} from "../../../../lib/js/src/Backend/Request";
import {ConsoleLogger} from "../../../../lib/js/src/Log/ConsoleLogger";
import {AppStorage} from "../../../../lib/js/src/Storage/AppStorage";
import {Router} from "../../../../lib/js/src/Route/Router";
import {Host} from "../../../../lib/js/src/Backend/Host";
import {Routes} from "./RegisterForm/Public/Routes";

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
            let _this = this;

            const request = new Request(
                new ConsoleLogger()
                , new AppStorage()
                , new Router(
                    new Host()
                )
            );

            const routes = new Routes();

            const register = new Register(
                request
                , routes
            );
            register.setup();

            $(_this.SELECTORS.REGISTER_BUTTON).click(function (event) {
                event.preventDefault();

                if (true === _this.registerButtonClicked) return;
                // that.disableForm();

                let firstName = $(_this.SELECTORS.FIRST_NAME).val();
                let lastName = $(_this.SELECTORS.LAST_NAME).val();
                let userName = $(_this.SELECTORS.USER_NAME).val();
                let email = $(_this.SELECTORS.EMAIL).val();
                let password = $(_this.SELECTORS.PASSWORD).val();
                let passwordRepeat = $(_this.SELECTORS.PASSWORD_REPEAT).val();
                let termsAndConditions = $(_this.SELECTORS.TERMS_AND_CONDITIONS).is(':checked');
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
