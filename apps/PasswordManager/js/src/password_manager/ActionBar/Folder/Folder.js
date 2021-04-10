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
import {RESPONSE_CODE_OK} from "../../../../../../../lib/js/src/Backend/Request";
import {NODE_ID_ROOT, STORAGE_ID_ROOT} from "../../Node/Node";

export class Folder {
    constructor(
        request
        , routes
        , node
        , longModal
        , templateParser
        , temporaryStorage
    ) {
        this.request = request;
        this.routes = routes;
        this.node = node;
        this.longModal = longModal;
        this.templateParser = templateParser;
        this.temporaryStorage = temporaryStorage;
    }

    handleClick(templates, strings) {
        const _this = this;

        $("#pwm__new__folder").off("click").off("one").one(
            "click",
            () => {
                _this.longModal.show(
                    strings.dialogTitle
                    , strings.submitText
                    , strings.negativeText
                    , _this.templateParser.parse(
                        templates["new-folder-template"]
                        , strings
                    )
                )
                    .then(
                        (modalBody) => {
                            const modalInputs = $(modalBody).find("form :input");
                            const title = $(modalInputs[0]).val().trim();

                            if ("" === title) {
                                _this.longModal.showError(strings['noTitleAlertText']);
                                return;
                            }

                            const parent = _this.temporaryStorage.get(STORAGE_ID_ROOT, NODE_ID_ROOT);

                            _this.request.post(
                                _this.routes.getPasswordManagerFolderCreate()
                                , {
                                    title: title
                                    , parent: parent
                                }
                                , (r) => {

                                    if (RESPONSE_CODE_OK in r) {
                                        _this.longModal.showSuccess(strings['insertedAlertText']);
                                        _this.node.loadDetails(parent);
                                        _this.handle();
                                    }
                                }
                            )
                        }
                    )
                ;

            });
    }

    handle() {
        const _this = this;

        Keestash.Main.readAssets()
            .then(
                (assets) => {
                    const templates = assets[0];
                    const strings = assets[1].password_manager;
                    _this.handleClick(templates, strings.strings.folder);
                })
    }
}
