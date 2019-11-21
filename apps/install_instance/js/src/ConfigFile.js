import modal from "../../../../lib/js/src/UI/modal";
import Formula from "../../../../lib/js/src/Formula";
import {Routes} from "./Routes";
import {RESPONSE_CODE_OK} from "../../../../lib/js/src/UI/ModalHandler";
import {Validator} from "./Validator";
import {DataNode} from "./DataNode";
import Parser from "../../../../lib/js/src/UI/Template/Parser";

export class ConfigFile {

    constructor() {
        this.element = $('#ii__config__segment');
        this.dimmer = $('#ii__config__segment__dimmer');
        this.formula = new Formula();
        this.routes = new Routes();
    }


    handle() {
        this.get();
    }

    get() {

        const _this = this;
        this.formula.get(
            this.routes.getConfigData()
            , {}
            , function (e) {
                const object = JSON.parse(e);


                if (RESPONSE_CODE_OK in object) {
                    const configDataString = object[RESPONSE_CODE_OK]['messages']['config_data'];
                    const configData = JSON.parse(configDataString);

                    const keys = Object.keys(configData);
                    const keysLength = keys.length;

                    const dataNode = new DataNode("install__instance__data__node");
                    const template = dataNode.getValue("data-config-template");
                    const strings = JSON.parse(dataNode.getValue("data-strings"));
                    const configStrings = strings.config;

                    if (0 === keysLength) {
                        _this.dimmer.remove();
                        _this.element.append(configStrings.nothingToUpdate).hide().fadeIn(500);
                        return;
                    }
                    const errors = configData[keys[0]];



                    const parsed = Parser.parse(
                        template
                        , {
                            instructions: errors
                            , dbHostLabel: configStrings.dbHostLabel
                            , dbHostPlaceholder: configStrings.dbHostPlaceholder
                            , dbHostDescription: configStrings.dbHostDescription
                            , dbUserLabel: configStrings.dbUserLabel
                            , dbUserPlaceholder: configStrings.dbUserPlaceholder
                            , dbUserDescription: configStrings.dbUserDescription
                            , dbPasswordLabel: configStrings.dbPasswordLabel
                            , dbPasswordPlaceholder: configStrings.dbPasswordPlaceholder
                            , dbPasswordDescription: configStrings.dbPasswordDescription
                            , dbNameLabel: configStrings.dbNameLabel
                            , dbNamePlaceholder: configStrings.dbNamePlaceholder
                            , dbNameDescription: configStrings.dbNameDescription
                            , dbPortLabel: configStrings.dbPortLabel
                            , dbPortPlaceholder: configStrings.dbPortPlaceholder
                            , dbPortDescription: configStrings.dbPortDescription
                            , dbCharsetLabel: configStrings.dbCharsetLabel
                            , dbCharsetPlaceholder: configStrings.dbCharsetPlaceholder
                            , dbCharsetDescription: configStrings.dbCharsetDescription
                            , logRequestsLabel: configStrings.logRequestsLabel
                            , enabledValue: configStrings.enabledValue
                            , enabled: configStrings.enabled
                            , disabledValue: configStrings.disabledValue
                            , disabled: configStrings.disabled
                            , dbLogRequestsDescription: configStrings.dbLogRequestsDescription
                            , submit: configStrings.submit
                        }
                    );

                    _this.dimmer.remove();
                    _this.element.append(parsed).hide().fadeIn(500);
                    _this.initFormSubmit();
                }

            }
            , function (e) {
                console.log(e)
            }
        )
    }


    initFormSubmit() {
        const formName = "#ii__config__form";
        const validator = new Validator();
        const _this = this;

        $(formName).ready(function () {
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


                _this.formula.post(
                    _this.routes.getInstallInstanceUpdateConfig()
                    , value
                    , function (x, y, z) {
                        const object = JSON.parse(x);

                        console.log(object);
                        if (RESPONSE_CODE_OK in object) {
                            _this.element.remove().fadeOut(1000);
                        }

                    }
                    , function (x, y, z) {
                        console.log(x)
                    }
                );

            })
        })
    }


}