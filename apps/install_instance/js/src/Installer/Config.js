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