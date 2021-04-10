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
import {RESPONSE_CODE_OK} from "../../../../../lib/js/src/Backend/Request";

export class EndUpdate {

    constructor(
        request
        , router
        , templateLoader
        , templateParser
        , routes
    ) {
        this.allEvents = {
            "config_data": false
            , "writable_dirs": false
            , "has_data_dirs": false
        };
        this.button = $("#ii__main__button");
        this.formula = request;
        this.router = router;
        this.routes = routes;
    }

    handle() {

        const _this = this;
        $(document).on("config_data", function () {
            _this.allEvents["config_data"] = true;
            _this.checkButtonState();
        });

        $(document).on("writable_dirs", function () {
            _this.allEvents["writable_dirs"] = true;
            _this.checkButtonState();
        });

        $(document).on("has_data_dirs", function () {
            _this.allEvents["has_data_dirs"] = true;
            _this.checkButtonState();
        });

        _this.listenToButton();
    }

    checkButtonState() {
        const _this = this;
        let allSet = true;

        $.each(_this.allEvents, function (i, v) {
            allSet = allSet && v;
        });

        if (true === allSet) {
            _this.button.removeClass("disabled");
        }
    }

    listenToButton() {
        const _this = this;
        _this.button.click((e) => {
            e.preventDefault();

            _this.button.addClass("loading");
            _this.button.addClass("disabled");

            window.setTimeout(
                () => {
                    _this.formula.post(
                        _this.routes.getInstallInstanceEndUpdate()
                        , {}
                        , function (x, y, z) {
                            const object = x;

                            if (RESPONSE_CODE_OK in object) {
                                const routeTo = object[RESPONSE_CODE_OK]['messages']['route_to'];
                                _this.router.route(routeTo);
                            }
                            _this.button.removeClass("loading");
                            _this.button.removeClass("disabled");
                        }
                        , function (x, y, z) {
                            _this.button.removeClass("loading");
                            _this.button.removeClass("disabled");
                            console.log(x);
                        }
                    );
                }, 500
            );
        })
    }


}
