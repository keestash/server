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
import {PasswordField} from "../../../../../Common/View/PasswordField";
import {RESPONSE_CODE_OK} from "../../../../../../../../../lib/js/src/Backend/Request";

export class PwGenerator {

    constructor(
        formula
        , routes
    ) {
        this.formula = formula;
        this.routes = routes;

    }

    init() {
        const range = $("#pwm__range__input");
        const upperCase = $("#pwm__upper__case");
        const lowerCase = $("#pwm__lower__case");
        const digit = $("#pwm__digit");
        const specialCharacter = $("#pwm__special__character");

        const elements = [
            range
            , upperCase
            , lowerCase
            , digit
            , specialCharacter
        ];

        for (let i = 0; i < elements.length; i++) {
            this.attachGeneratorEvent(elements[i]);
        }

        this.attachUseEvent();
    }

    attachUseEvent() {
        const button = $("#pwm__use__button");
        const generatorInput = $("#pwm__generator__input");

        button.on(
            "click"
            , (event) => {
                event.preventDefault();
                const generatorValue = generatorInput.val();
                if ("" === generatorValue || typeof generatorValue === 'undefined' || null === generatorValue) return;
                const passwordField = new PasswordField("#pwm__login__password");
                passwordField.setValue(generatorValue);
                passwordField.show();
            });

    }

    attachGeneratorEvent(element) {
        const _this = this;
        element.ready(
            () => {

                element.on(
                    'change'
                    , () => {
                        const val = $("#pwm__range__input").val();

                        const upperCase = $("#pwm__upper__case").prop('checked') || false;
                        const lowerCase = $("#pwm__lower__case").prop('checked') || false;
                        const digit = $("#pwm__digit").prop('checked') || false;
                        const specialChar = $("#pwm__special__character").prop('checked') || false;

                        const route = _this.routes.getGeneratePassword(
                            val
                            , upperCase
                            , lowerCase
                            , digit
                            , specialChar
                        );

                        _this.formula.get(
                            route
                            , {}
                            , (x, y, z) => {
                                const object = (x);

                                if (!(RESPONSE_CODE_OK in object)) {
                                    return;
                                }

                                const strings = object[RESPONSE_CODE_OK]['messages']['response']['strings'];
                                const value = object[RESPONSE_CODE_OK]['messages']['response']['password']['value'];
                                const quality = object[RESPONSE_CODE_OK]['messages']['response']['password']['quality'];
                                const entropy = object[RESPONSE_CODE_OK]['messages']['response']['password']['entropy'];
                                const qualityString = strings['quality'];

                                $("#pwm__generator__input").val(value);
                                $("#pwm__quality__label").html(qualityString[quality]);
                                $("#pwm__entropy__value").html(entropy.toFixed(2));
                                $("#pwm__character__count").html(value.length);
                            }
                            , (x, y, z) => {
                                console.log(x);
                            }
                        )

                    });

            });
    }
}
