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
import {NODE_ID_ROOT, STORAGE_ID_ROOT} from "../../Node/Node";
import {RESPONSE_CODE_NOT_OK, RESPONSE_CODE_OK} from "../../../../../../../lib/js/src/Backend/Request";

export class Credential {

    constructor(
        templateLoader
        , longModal
        , request
        , routes
        , node
        , stringLoader
        , parser
        , temporaryStorage
    ) {
        this.templateLoader = templateLoader;
        this.longModal = longModal;
        this.request = request;
        this.routes = routes;
        this.node = node;
        this.stringLoader = stringLoader;
        this.parser = parser;
        this.temporaryStorage = temporaryStorage;
    }

    handle() {
        const _this = this;
        Keestash.Main.readAssets()
            .then((assets) => {
                const templates = assets[0];
                const strings = assets[1];

                _this.handleNewPassword(
                    templates
                    , strings.password_manager.strings.credential
                );
            })
    }

    handleNewPassword(
        templates
        , credentialStrings
    ) {
        const _this = this;
        $("#pwm__new__password").off("click").off("one").one("click", () => {
            const template = templates["new-password-template"];

            _this.longModal.show(
                credentialStrings.dialogTitle
                , credentialStrings.submitText
                , credentialStrings.negativeText
                , this.parser.parse(template, credentialStrings)
            )
                .then(
                    (modalBody) => {

                        const modalInputs = $(modalBody).find("form :input");
                        const title = $(modalInputs[0]).val().trim();
                        const userName = $(modalInputs[1]).val().trim();
                        const password = $(modalInputs[2]).val().trim();
                        const url = $(modalInputs[3]).val().trim();
                        const notes = $(modalInputs[4]).val().trim();

                        if ("" === title) {
                            _this.longModal.showError(credentialStrings['noTitleAlertText']);
                            return;
                        }

                        const parent = _this.temporaryStorage.get(STORAGE_ID_ROOT, NODE_ID_ROOT);
                        let data = {
                            title: title
                            , user_name: userName
                            , password: password
                            , url: url
                            , notes: notes
                            , parent: parent
                        };

                        _this.request.post(
                            _this.routes.getPasswordManagerCreate()
                            , data
                            , (x, y, z) => {
                                let json = x;

                                if (RESPONSE_CODE_OK in json) {
                                    _this.longModal.showSuccess(
                                        json[RESPONSE_CODE_OK]["messages"]["message"]
                                    )

                                    _this.node.loadDetails(
                                        parent
                                    )
                                    _this.handle();
                                } else if (RESPONSE_CODE_NOT_OK in json) {
                                    _this.longModal.showSuccess(
                                        json[RESPONSE_CODE_NOT_OK]["messages"]["message"]
                                    )
                                } else {
                                    console.log(x)
                                }
                            }
                            , (x, y, z) => {
                                console.log(x);
                            }
                        )

                    }
                );

        });
    }

}
