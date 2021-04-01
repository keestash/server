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
import FormData from "form-data";
import {RESPONSE_CODE_OK} from "../../../../../../../../../lib/js/src/Backend/Request";

export class Attachments {
    constructor(
        appStorage
        , request
        , routes
        , arrayListService
        , templateParser
        , miniModal
    ) {
        this.storage = appStorage;
        this.request = request;
        this.routes = routes;
        this.arrayListService = arrayListService;
        this.templateParser = templateParser;
        this.miniModal = miniModal;
        this.element = $("#pwm__attachments__input");
    }

    init(node, strings, templates) {
        this.initAttachments(node, strings, templates);
        this.initInputChangeListener(node, strings, templates);
    }

    initAttachments(node, strings, templates) {

        const _this = this;

        _this.element.ready(
            () => {

                _this.request.get(
                    _this.routes.getAttachments(node.id)
                    , {}
                    , (x, y, z) => {
                        const object = JSON.parse(x);

                        if (RESPONSE_CODE_OK in object) {
                            let fileList = object[RESPONSE_CODE_OK]['messages']['fileList'];
                            const icons = object[RESPONSE_CODE_OK]['messages']['icons'];

                            fileList = _this.arrayListService.excludeNullValues(fileList.content);
                            let parsed = null;

                            if (0 === fileList.length) {
                                parsed = _this.parseEmptyAttachments(strings, templates)
                            } else {
                                const parsedArray = _this.parseAttachments(
                                    fileList
                                    , strings
                                    , templates
                                    , icons
                                );
                                parsed = parsedArray.join('');
                            }

                            $("#attachments__results").find("ul").html(parsed);
                            _this.parseRemoveListener();
                        }
                    }
                    , (x, y, z) => {
                        console.log(x)
                    }
                )
            });
    }

    parseEmptyAttachments(strings, templates) {
        return this.templateParser.parse(
            templates['no-attachments']
            , {
                noAttachments: strings["credential"]["attachments"]["noAttachments"]
            }
        )
    }

    parseAttachments(nodeFiles, strings, templates, icons) {
        const parsed = [];
        for (let i = 0; i < nodeFiles.length; i++) {
            const data = this.templateParser.parse(
                templates["attachment"]
                , {
                    nodeFile: nodeFiles[i]
                    , icons: icons
                    , misc: {
                        attachment: this.routes.getNodeAttachment(nodeFiles[i].file.id)
                    }
                }
            );
            parsed.push(data);
        }
        return parsed;
    }

    parseRemoveListener() {
        const _this = this;
        $("#attachments__results").find(".remove").off("click").one(
            "click"
            , (e) => {
                const target = $(e.target);
                const fileId = target.data("file-id");
                _this.miniModal.show(
                    'Do you really wanna delete'
                    , 'Yes'
                    , 'No'
                    , 'This file is going to be deleted'
                ).then(
                    (modal) => {

                        _this.request.post(
                            _this.routes.getNodeAttachmentRemove()
                            , {
                                fileId: fileId
                            }
                            , (response) => {
                                const object = JSON.parse(response);

                                if (RESPONSE_CODE_OK in object) {

                                    const children = $("#attachments__results").find("ul").children();
                                    const removedFile = object[RESPONSE_CODE_OK]["messages"]["file"];

                                    $.each(
                                        children
                                        , (i, v) => {
                                            const li = $(v);
                                            const fileId = li.data("file-id");

                                            if (fileId === removedFile["id"]) {
                                                li.remove();
                                            }

                                        }
                                    )

                                    modal.modal('hide');
                                }
                            }
                            , (error) => {
                                console.log(error);
                            }
                        )
                    }
                )
            }
        )
    }

    initInputChangeListener(node, strings, templates) {
        const _this = this;

        _this.element.change(
            () => {
                const formData = new FormData();
                const files = document.getElementById('pwm__attachments__input').files;
                const fileLength = files.length;

                formData.append("node_id", node.id);
                for (let i = 0; i < fileLength; i++) {
                    formData.append("files[]", files[i]);
                }

                // AJAX request
                $.ajax({
                    url: _this.routes.putAttachments(_this.storage.getToken(), _this.storage.getUserHash()),
                    type: 'post',
                    data: formData,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: (object) => {
                        const files = object[RESPONSE_CODE_OK]['messages']['files'];
                        const icons = object[RESPONSE_CODE_OK]['messages']['icons'];

                        const parsed = _this.parseAttachments(files, strings, templates, icons)
                        $("#attachments__results").find("ul").append(parsed);
                        _this.parseRemoveListener();
                        _this.element.val('');
                    }
                });
            });
    }
}
