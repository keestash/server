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
import {Base, CONFIG_DATA} from "./Base";
import {Validator} from "../Validator";
import modal from "../../../../../lib/js/src/UI/modal";
import {RESPONSE_CODE_OK} from "../../../../../lib/js/src/UI/ModalHandler";

export class Config extends Base {

    constructor(formula, routes) {

        super(
            "ii__config__segment"
            , "ii__config__segment__dimmer"
            , formula
            , {
                "name": CONFIG_DATA
                , "route": routes.getConfigData()
                , "template_name": "config_template"
            }
        );

        this.routes = routes;

    }

    initFormSubmit(strings) {
        const validator = new Validator();
        const _this = this;

        $("#ii__config__part__submit").ready(function () {
            $("#ii__config__part__submit").click(function (e) {
                e.preventDefault();

                const host = validator.getValIfExists("ii__db__host");
                const user = validator.getValIfExists("ii__db__user");
                const password = validator.getValIfExists("ii__db__password");
                const name = validator.getValIfExists("ii__db__name");
                const port = validator.getValIfExists("ii__db__port");
                const charset = validator.getValIfExists("ii__db__charset");
                const logRequests = validator.getValIfExists("ii__log__requests");

                const hostValid = validator.isValid(host, "ii__db__host");
                const userValid = validator.isValid(user, "ii__db__user");
                // const passwordValid = validator.isValid(password, "ii__db__password");
                const nameValid = validator.isValid(name, "ii__db__name");
                const portValid = validator.isValid(port, "ii__db__port");
                const charsetValid = validator.isValid(charset, "ii__db__charset");
                const lgValid = validator.isValidSelect(logRequests, "ii__log__requests");

                if (
                    false === hostValid ||
                    false === userValid ||
                    // false === passwordValid ||
                    false === nameValid ||
                    false === portValid ||
                    false === charsetValid ||
                    false === lgValid
                ) {
                    modal.miniModal("please edit");
                    return;
                }

                const value = {
                    host: host
                    , user: user
                    , password: password
                    , schema_name: name
                    , port: port
                    , charset: charset
                    , log_requests: logRequests
                };


                _this.formula.post(
                    _this.routes.getInstallInstanceUpdateConfig()
                    , value
                    , function (x, y, z) {
                        const object = JSON.parse(x);

                        if (RESPONSE_CODE_OK in object) {
                            _this.parent.remove().fadeOut(3000);
                            _this.parent.append(strings.updated);
                            _this.triggerEvent();
                        }

                    }
                    , function (x, y, z) {
                        console.log(x)
                    }
                );

            })
        });

        super.initFormSubmit();
    }

}