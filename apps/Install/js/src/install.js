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

import {Page} from "./Install/Page";
import {Router} from "../../../../lib/js/src/Route/Router";
import {Host} from "../../../../lib/js/src/Backend/Host";
import {Routes} from "./Public/Routes";
import {AXIOS} from "../../../../lib/js/src/StartUp";

(function () {
    if (!Keestash.Apps.Install) {
        Keestash.Apps.Install = {};
    }

    Keestash.Apps.Install = {

        init: function () {

            const container = Keestash.Main.getContainer();
            const router = new Router(
                new Host()
            );

            const page = new Page(
                container.query(AXIOS)
                , new Routes()
                , router
            );
            page.run();
        }


    }

})();

$(document).ready(function () {
    Keestash.Apps.Install.init();
});

