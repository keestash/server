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
import {RESPONSE_CODE_NOT_OK, RESPONSE_CODE_OK} from "../../../../../../lib/js/src/UI/ModalHandler";

export class Submit {

    constructor(
        router
        , request
        , appStorage
        , routes
        , globalRoutes
        , templateLoader
        , inputService
        , buttonService
        , miniModal
    ) {
        this.router = router;
        this.request = request;
        this.appStorage = appStorage;
        this.routes = routes;
        this.globalRoutes = globalRoutes;
        this.templateLoader = templateLoader;
        this.inputService = inputService;
        this.buttonService = buttonService;
        this.miniModal = miniModal;
    }

    handle() {
        const _this = this;

        $("#loginform").submit(
            (event) => {
                event.preventDefault();

                const user = $("#username");
                const password = $("#password");
                const signIn = $("#sign__in");

                _this.buttonService.disable(signIn, true);

                if ("" === user.val().trim()) {
                    _this.inputService.invalid(user);
                    return;
                }

                if ("" === password.val().trim()) {
                    _this.inputService.invalid(password);
                    return;
                }

                const data = {
                    'user': user.val().trim()
                    , 'password': password.val().trim()
                };

                _this.request.post(
                    _this.routes.getLoginSubmit()
                    , data
                    , (html, status, xhr) => {
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

                            _this.buttonService.disable(
                                signIn
                                , false
                            );

                            _this.templateLoader.load(true).finally(() => {
                                _this.router.routeTo(routeTo);
                            });

                            return;
                        } else if (RESPONSE_CODE_NOT_OK in object) {
                            result_object = object[RESPONSE_CODE_NOT_OK];
                            _this.miniModal.show(
                                'Error'
                                , 'Ok'
                                , 'Not Ok'
                                , result_object['message']
                                , 'event'
                            );
                            _this.appStorage.clearAPICredentials();
                            _this.buttonService.disable(
                                signIn
                                , false
                            );
                        }

                        if (result_object === null) {
                            _this.miniModal.show(
                                'Error'
                                , 'Ok'
                                , 'Not Ok'
                                , "There was an error. Please try again or contact our support"
                                , 'event'
                            );
                            _this.appStorage.clearAPICredentials();
                        }
                        _this.buttonService.disable(
                            signIn
                            , true
                        );
                    }
                    , (html, status, xhr) => {
                        _this.miniModal.show(
                            'Error'
                            , 'Ok'
                            , 'Not Ok'
                            , "There was an error. Please try again or contact our support"
                            , 'event'
                        );
                        _this.appStorage.clearAPICredentials();
                        _this.buttonService.disable(
                            signIn
                            , false
                        );
                    }
                );
            });


    }

}
