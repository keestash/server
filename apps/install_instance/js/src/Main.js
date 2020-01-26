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
import Formula from "../../../../lib/js/src/Formula";
import {Routes} from "./Routes";
import {RESPONSE_CODE_OK} from "../../../../lib/js/src/UI/ModalHandler";
import {Router} from "../../../../lib/js/src/Route/Router";

/**
 * @deprecated
 */
export class Main {
    handle() {
        console.log("i could be deprecated");

        $("#ii__main__button").click(function (e) {
            e.preventDefault();

            const formula = new Formula();
            const routes = new Routes();

            window.setTimeout(
                function () {
                    formula.post(
                        routes.getInstallInstanceEndUpdate()
                        , {}
                        , function (x, y, z) {
                            const object = JSON.parse(x);

                            if (RESPONSE_CODE_OK in object) {
                                const router = new Router(
                                    Keestash.Main.getHost()
                                );
                                const routeTo = object[RESPONSE_CODE_OK]['messages']['route_to'];
                                router.route(routeTo);
                            }
                        }
                        , function (x, y, z) {
                            console.log(x);
                        }
                    );
                }, 500
            );
        })
    }
}