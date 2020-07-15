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
import {Base, HAS_DATA_DIRS, WRITABLE_DIRS} from "./Base";
import {RESPONSE_CODE_OK} from "../../../../../lib/js/src/UI/ModalHandler";
import modal from "../../../../../lib/js/src/UI/modal";

export class HasDataDirs extends Base {

    constructor(
        request
        , lazyOperator
        , templateLoader
        , templateParser
        , routes
    ) {
        super(
            "ii__has__data__dirs__segment"
            , "ii__has__data__dirs__segment__dimmer"
            , request
            , templateLoader
            , templateParser
            , {
                "name": HAS_DATA_DIRS
                , "route": routes.getHasDataDirs()
                , "template_name": "has_data_dirs"
            }
        );

        this.routes = routes;
        this.lazyOperator = lazyOperator;
        this.buttonId = "#ii__has__data__dirs__submit";
        this.listenToEvent();
    }

    listenToEvent() {
        const _this = this;
        $(document).on(WRITABLE_DIRS, function () {

            _this.lazyOperator.doAfterElementDisplayed(
                _this.buttonId
                , 250
                , function (selector) {
                    $(selector).removeClass("disabled");
                }
            );

        });
    }

    initFormSubmit(strings) {
        const _this = this;

        $(_this.buttonId).ready(function () {
            $(_this.buttonId).click(function (e) {
                e.preventDefault();

                _this.formula.get(
                    _this.getKeys().route
                    , {}
                    , function (x, y, z) {
                        const object = JSON.parse(x);

                        if (RESPONSE_CODE_OK in object) {

                            const messages = object[RESPONSE_CODE_OK]['messages'];
                            let earlyReturn = false;

                            if (HAS_DATA_DIRS in messages) {
                                let l = JSON.parse(messages[HAS_DATA_DIRS]).length;

                                if (l !== 0) {
                                    alert('still not there! Please check again!');
                                    earlyReturn = true;
                                }
                            }

                            if (true === earlyReturn) {
                                return;
                            }

                            $(this).parent().remove().fadeOut(3000);
                            $(this).parent().append(strings.updated);
                            _this.triggerEvent();
                        } else {
                            modal.miniModal("The dirs are still not readable/writable. Please try again!");
                        }
                    }
                    , function (x, y, z) {
                        console.log(x)
                    }
                )
            });
        });
    }
}
