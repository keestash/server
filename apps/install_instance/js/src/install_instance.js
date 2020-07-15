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
import {Routes} from "./Routes";
import {HasDataDirs} from "./Installer/HasDataDirs";
import {EndUpdate} from "./Installer/EndUpdate";
import {
    LAZY_OPERATOR,
    MINI_MODAL,
    REQUEST,
    ROUTER,
    TEMPLATE_LOADER,
    TEMPLATE_PARSER
} from "../../../../lib/js/src/StartUp";

(function () {
    if (!Keestash.Apps.InstallInstance) {
        Keestash.Apps.InstallInstance = {};
    }

    Keestash.Apps.InstallInstance = {

        init: () => {
            const routes = new Routes();
            const diContainer = Keestash.Main.getContainer();

            const handler = [

                new Config(
                    diContainer.query(REQUEST)
                    , diContainer.query(TEMPLATE_LOADER)
                    , diContainer.query(TEMPLATE_PARSER)
                    , diContainer.query(MINI_MODAL)
                    , routes
                )

                , new DirsWritable(
                    diContainer.query(REQUEST)
                    , diContainer.query(LAZY_OPERATOR)
                    , diContainer.query(TEMPLATE_LOADER)
                    , diContainer.query(TEMPLATE_PARSER)
                    , routes
                )

                , new HasDataDirs(
                    diContainer.query(REQUEST)
                    , diContainer.query(LAZY_OPERATOR)
                    , diContainer.query(TEMPLATE_LOADER)
                    , diContainer.query(TEMPLATE_PARSER)
                    , routes
                )

                , new EndUpdate(
                    diContainer.query(REQUEST)
                    , diContainer.query(ROUTER)
                    , diContainer.query(TEMPLATE_LOADER)
                    , diContainer.query(TEMPLATE_PARSER)
                    , routes
                )

            ];

            for (let i = 0; i < handler.length; i++) {
                handler[i].handle();
            }

        }

    }

})();

$(document).ready(() => {
    Keestash.Apps.InstallInstance.init();
});

