import modal from "../../../../lib/js/src/UI/modal";
import Formula from "../../../../lib/js/src/Formula";
import {Routes} from "./Routes";
import {RESPONSE_CODE_OK} from "../../../../lib/js/src/UI/ModalHandler";
import {Validator} from "./Validator";

export class ConfigFile {

    handle() {
        const formName = "#ii__config__form";
        const validator = new Validator();

        $(formName).submit(function (e) {
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

            const formula = new Formula();
            const routes = new Routes();

            formula.post(
                routes.getInstallInstanceUpdateConfig()
                , value
                , function (x, y, z) {
                    const object = JSON.parse(x);

                    console.log(object);
                    if (RESPONSE_CODE_OK in object) {

                        $("#config__part").fadeOut(1000, function () {
                            $(this).remove();
                        });
                    }

                }
                , function (x, y, z) {
                    console.log(x)
                }
            );

        })
    }

}