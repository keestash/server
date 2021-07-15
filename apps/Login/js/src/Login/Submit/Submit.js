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
import {RESPONSE_CODE_NOT_OK, RESPONSE_CODE_OK} from "../../../../../../lib/js/src/Backend/Request";
import {HEADER_NAME_TOKEN, HEADER_NAME_USER, RESPONSE_FIELD_MESSAGES} from "../../../../../../lib/js/src/Backend/Axios";

export class Submit {

    constructor(
        router
        , axios
        , appStorage
        , routes
        , globalRoutes
        , templateLoader
        , inputService
        , buttonService
        , miniModal
    ) {
        this.router = router;
        this.axios = axios;
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

                _this.axios.post(
                    _this.routes.getLoginSubmit()
                    , data
                )
                    .then((response) => {
                        if (RESPONSE_CODE_NOT_OK in response.data) {
                            return []
                        }

                        return {
                            data: response.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES]
                            , headers: {
                                [HEADER_NAME_TOKEN]: response.headers[HEADER_NAME_TOKEN]
                                , [HEADER_NAME_USER]: response.headers[HEADER_NAME_USER]
                            }
                        };
                    })
                    .then((data) => {
                        if (0 === data.length) {
                            alert("no data!");
                            _this.appStorage.clearAPICredentials();
                            _this.buttonService.disable(
                                signIn
                                , true
                            );
                            return;
                        }

                        _this.appStorage.storeAPICredentials(
                            data.headers[HEADER_NAME_TOKEN]
                            , data.headers[HEADER_NAME_USER]
                        );

                        _this.appStorage.storeLocale(data.data.settings.locale);
                        _this.appStorage.storeLanguage(data.data.settings.language);

                        _this.buttonService.disable(
                            signIn
                            , false
                        );

                        _this.router.routeTo(data.data.routeTo);

                    })
                    .catch((data) => {
                        _this.miniModal.show(
                            'Error'
                            , 'Ok'
                            , 'Not Ok'
                            , "There was an error. Please try again or contact our support"
                        );
                        _this.appStorage.clearAPICredentials();
                        _this.buttonService.disable(
                            signIn
                            , false
                        );
                    })
            });


    }

}
