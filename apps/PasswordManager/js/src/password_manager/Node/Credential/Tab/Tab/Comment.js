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
import {RESPONSE_CODE_OK} from "../../../../../../../../../lib/js/src/Backend/Request";

export class Comment {

    constructor(
        request
        , routes
        , appStorage
        , arrayListService
        , templateParser
    ) {
        this.newCommentAreaId = "#pwm__new__comment__form";
        this.commentBox = "#comment__result";
        this.request = request;
        this.routes = routes;
        this.appStorage = appStorage;
        this.arrayListService = arrayListService;
        this.templateParser = templateParser;
    }

    init(node, strings, templates) {
        this.initComments(node, strings, templates);
        this.initNewComment(node);
    }

    initComments(node, strings, templates) {
        const _this = this;
        const element = $(this.commentBox);
        element.ready(
            () => {

                _this.request.get(
                    _this.routes.getComments(node.id)
                    , {}
                    , (x, y, z) => {
                        const object = JSON.parse(x);

                        if (RESPONSE_CODE_OK in object) {
                            let comments = object[RESPONSE_CODE_OK]["messages"]["comments"];
                            comments = _this.arrayListService.excludeNullValues(comments.content);
                            let parsed = null;

                            if (0 === comments.length) {
                                parsed = _this.parseEmptyComments(strings, templates)
                            } else {
                                const parsedArray = _this.parseComments(
                                    comments
                                    , strings
                                    , templates
                                );
                                parsed = parsedArray.join('');

                            }

                            $("#comment__result").find("ul").html(parsed);
                            // _this.parseRemoveListener();

                        }
                    }
                    , (x, y, z) => {
                        console.log(x)
                    }
                )
            });
    }

    parseEmptyComments(strings, templates) {
        return this.templateParser.parse(
            templates['no-comments']
            , {
                noComments: strings["credential"]["comment"]["noComment"]
            }
        )
    }

    parseComments(comments, strings, templates) {
        const parsed = [];

        for (let i = 0; i < comments.length; i++) {
            const data = this.templateParser.parse(
                templates["comment"]
                , {
                    comment: comments[i]
                }
            );
            parsed.push(data);
        }
        return parsed;

    }

    initNewComment() {
        const _this = this;
        const commentBox = $("#pwm__notes__note__area");
        commentBox.ready(
            () => {
                const addButton = $("#pwm__notes__note");
                addButton.one("click", (event) => {
                    event.preventDefault();
                    const text = commentBox.val();
                    const userId = addButton.attr("data-user-id");
                    const nodeId = addButton.attr("data-node-id");

                    if ("" === text) {
                        return;
                    }
                    if ("" === userId) {
                        return;
                    }
                    if ("" === nodeId) {
                        return;
                    }

                    _this.request.post(
                        _this.routes.getAddComment()
                        , {
                            user_id: userId
                            , node_id: nodeId
                            , comment: text
                        }
                        , function (x, y, z) {
                            const object = JSON.parse(x);

                            if (RESPONSE_CODE_OK in object) {
                                const comment = object[RESPONSE_CODE_OK]['messages']['comment'];
                            }

                            commentBox.val("");
                        }
                        , function (x, y, z) {
                            console.log(x)
                        }
                    )
                })
            });
    }
}
