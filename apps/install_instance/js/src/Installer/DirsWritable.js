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
import {Base, CONFIG_DATA, READABLE_DIRS, WRITABLE_DIRS} from "./Base";
import {RESPONSE_CODE_OK} from "../../../../../lib/js/src/UI/ModalHandler";
import modal from "../../../../../lib/js/src/UI/modal";

export class DirsWritable extends Base {

    constructor(
        formula
        , routes
        , lazyOperator
    ) {
        super(
            "ii__dirs__writable__segment"
            , "ii__dirs__writable__segment__dimmer"
            , formula
            , {
                "name": WRITABLE_DIRS
                , "route": routes.getDirsWritableData()
                , "template_name": "dirs_writable_template"
            }
        )
        ;
        this.routes = routes;
        this.lazyOperator = lazyOperator;
        this.listenToEvent();
    }

    listenToEvent() {
        const _this = this;
        $(document).on(CONFIG_DATA, function () {

            _this.lazyOperator.doAfterElementDisplayed(
                "#ii__dirs__writable__submit"
                , 250
                , function (selector) {
                    $(selector).removeClass("disabled");
                }
            );

        });
    }

    initFormSubmit(strings) {
        const _this = this;

        $("#ii__dirs__writable__submit").ready(function () {
            $("#ii__dirs__writable__submit").click(function (e) {
                e.preventDefault();

                _this.formula.get(
                    _this.getKeys().route
                    , {}
                    , function (x, y, z) {
                        const object = JSON.parse(x);
                        console.log(object);

                        if (RESPONSE_CODE_OK in object) {

                            const messages = object[RESPONSE_CODE_OK]['messages'];
                            let earlyReturn = false;

                            if (WRITABLE_DIRS in messages) {
                                let l = JSON.parse(messages[WRITABLE_DIRS]).length;

                                if (l !== 0) {
                                    alert('still not there! Please check again!');
                                    earlyReturn = true;
                                }
                            }

                            if (READABLE_DIRS in messages) {
                                let l = JSON.parse(messages[READABLE_DIRS]).length;

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