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
import Input from "../../../../../../lib/js/src/UI/Input";
import {RESPONSE_CODE_NOT_OK, RESPONSE_CODE_OK} from "../../../../../../lib/js/src/UI/ModalHandler";
import modal from "../../../../../../lib/js/src/UI/modal";

export class Submit {

    constructor(
        router
        , request
        , appStorage
        , routes
        , globalRoutes
        , templateLoader
    ) {
        this.router = router;
        this.request = request;
        this.appStorage = appStorage;
        this.routes = routes;
        this.globalRoutes = globalRoutes;
        this.templateLoader = templateLoader;
    }

    handle() {
        const _this = this;

        $("#login").submit(function (event) {
            event.preventDefault();
            _this.changeButtonState(false);

            let user = $("#username").val();
            let password = $("#password").val();

            if (user === "") {
                Input.invalid("#username");
                _this.changeButtonState(true);
                return;
            }

            if (password === "") {
                Input.invalid("#password");
                _this.changeButtonState(true);
                return
            }

            let data = {
                'user': user
                , 'password': password
            };

            _this.request.post(
                _this.routes.getLoginSubmit()
                , data
                , function (html, status, xhr) {
                    let object = JSON.parse(html);
                    let result_object = null;

                    if (RESPONSE_CODE_OK in object) {
                        result_object = object[RESPONSE_CODE_OK];
                        let routeTo = result_object['routeTo'];
                        let token = xhr.getResponseHeader('api_token');
                        let userHash = xhr.getResponseHeader('user_hash');

                        _this.appStorage.storeAPICredentials(
                            token
                            , userHash
                        );

                        _this.changeButtonState(true);

                        _this.templateLoader.load(true).finally(() => {
                            _this.router.routeTo(routeTo);
                        });

                        // _this.request.get(
                        //     _this.globalRoutes.getAllTemplates()
                        //     , {}
                        //     , (x, y, z) => {
                        //         const isJson = Util.isJson(x);
                        //         if (true === isJson) {
                        //             const object = JSON.parse(x);
                        //
                        //             if (RESPONSE_CODE_OK in object) {
                        //                 const templateStorage = new TemplateStorage();
                        //                 const templates = object[RESPONSE_CODE_OK]["messages"]["templates"];
                        //
                        //                 templateStorage.addAll(templates)
                        //                     .catch(() => {
                        //                         console.log("error :(")
                        //                     })
                        //                     .finally(() => {
                        //
                        //                     });
                        //
                        //             }
                        //         }
                        //
                        //     }
                        // );
                        return;
                    } else if (RESPONSE_CODE_NOT_OK in object) {
                        result_object = object[RESPONSE_CODE_NOT_OK];
                        modal.miniModal(result_object['message']);
                        _this.appStorage.clearAPICredentials();
                        _this.changeButtonState(true);
                    }

                    if (result_object === null) {
                        modal.miniModal("There was an error. Please try again or contact our support");
                        _this.appStorage.clearAPICredentials();
                    }
                    _this.changeButtonState(true);
                }
                , function (html, status, xhr) {
                    modal.miniModal("There was an error. Please try again or contact our support")
                    _this.appStorage.clearAPICredentials();
                    _this.changeButtonState(true);
                }
            );
        });


    }

    changeButtonState(enable) {

        let button = $("#tl__login__button");

        button.addClass("disabled");
        if (true === enable) {
            button.removeClass("disabled");
        }
    }
}