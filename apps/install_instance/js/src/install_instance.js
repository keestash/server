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
import {Config} from "./Installer/Config";
import {DirsWritable} from "./Installer/DirsWritable";
import Formula from "../../../../lib/js/src/Formula";
import {Routes} from "./Routes";
import {HasDataDirs} from "./Installer/HasDataDirs";
import {EndUpdate} from "./Installer/EndUpdate";
import {LazyOperator} from "../../../../lib/js/src/Util/LazyOperator";
import {Router} from "../../../../lib/js/src/Route/Router";

(function () {
    if (!Keestash.Apps.InstallInstance) {
        Keestash.Apps.InstallInstance = {};
    }

    Keestash.Apps.InstallInstance = {

        init: function () {
            const formula = new Formula();
            const routes = new Routes();
            const lazyOperator = new LazyOperator();
            const router = new Router(
                Keestash.Main.getHost()
            );

            const handler = [

                new Config(
                    formula
                    , routes
                )

                , new DirsWritable(
                    formula
                    , routes
                    , lazyOperator
                )
                , new HasDataDirs(
                    formula
                    , routes
                    , lazyOperator
                )
                , new EndUpdate(
                    formula
                    , routes
                    , router
                )
            ];

            for (let i = 0; i < handler.length; i++) {
                handler[i].handle();
            }


        }


    }

})();

$(document).ready(function () {
    Keestash.Apps.InstallInstance.init();
});

