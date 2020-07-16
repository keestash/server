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
import {RESPONSE_CODE_OK} from "../../../../../lib/js/src/UI/ModalHandler";

export class Config extends Base {

    constructor(
        request
        , templateLoader
        , templateParser
        , modal
        , routes
    ) {

        super(
            "ii__config__segment"
            , "ii__config__segment__dimmer"
            , request
            , templateLoader
            , templateParser
            , {
                "name": CONFIG_DATA
                , "route": routes.getConfigData()
                , "template_name": "config_part"
            }
        );

        this.routes = routes;
        this.modal = modal;

    }

    initFormSubmit(strings) {
        const validator = new Validator();
        const _this = this;
        const button = $("#ii__config__part__submit");

        button.ready(() => {
            button.click((e) => {
                e.preventDefault();

                const host = validator.getValIfExists("ii__db__host");
                const user = validator.getValIfExists("ii__db__user");
                const password = validator.getValIfExists("ii__db__password");
                const name = validator.getValIfExists("ii__db__name");
                const port = validator.getValIfExists("ii__db__port");
                const charset = validator.getValIfExists("ii__db__charset");
                const logRequests = validator.getValIfExists("ii__log__requests");
                const smtpHost = validator.getValIfExists("ii__email__smtp__host");
                const smtpUser = validator.getValIfExists("ii__email__user");
                const smtpPassword = validator.getValIfExists("ii__email__password");

                const hostValid = validator.isValid(host, "ii__db__host");
                const userValid = validator.isValid(user, "ii__db__user");
                // const passwordValid = validator.isValid(password, "ii__db__password");
                const nameValid = validator.isValid(name, "ii__db__name");
                const portValid = validator.isValid(port, "ii__db__port");
                const charsetValid = validator.isValid(charset, "ii__db__charset");
                const lgValid = validator.isValidSelect(logRequests, "ii__log__requests");
                const smtpHostValid = validator.isValid(smtpHost, "ii__email__smtp__host");
                const smtpUserValid = validator.isValid(smtpUser, "ii__email__user");
                const smtpPasswordValid = validator.isValid(smtpPassword, "ii__email__password");

                if (
                    false === hostValid
                    || false === userValid
                    || false === nameValid
                    || false === portValid
                    || false === charsetValid
                    || false === lgValid
                    || false === smtpHostValid
                    || false === smtpUserValid
                    || false === smtpPasswordValid
                ) {
                    _this.modal.show(
                        'Missing Data'
                        , 'Ok'
                        , 'Close'
                        , 'Some Data are missing. Please edit'
                        , 'new.event'
                    );
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
                    , smtp_host: smtpHost
                    , smtp_user: smtpUser
                    , smtp_password: smtpPassword
                };

                _this.formula.post(
                    _this.routes.getInstallInstanceUpdateConfig()
                    , value
                    , function (x, y, z) {
                        const object = JSON.parse(x);

                        if (RESPONSE_CODE_OK in object) {
                            _this.removeAllExceptFirst(
                                _this.parent
                            )
                            // _this.parent.children().remove().fadeOut(3000);
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
