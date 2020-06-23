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
import $ from 'jquery';
import {Register} from "./RegisterForm/Register";
import {Request} from "../../../../lib/js/src/Backend/Request";
import {ConsoleLogger} from "../../../../lib/js/src/Log/ConsoleLogger";
import {AppStorage} from "../../../../lib/js/src/Storage/AppStorage";
import {Router} from "../../../../lib/js/src/Route/Router";
import {Host} from "../../../../lib/js/src/Backend/Host";
import {Routes} from "./RegisterForm/Public/Routes";
import {UIService} from "../../../../lib/js/src/Service/UI/UIService";

(function () {
    if (!Keestash.Register) {
        Keestash.Register = {};
    }

    Keestash.Register = {

        init: async () => {

            const request = new Request(
                new ConsoleLogger()
                , new AppStorage()
                , new Router(
                    new Host()
                )
            );

            const routes = new Routes();
            const uiService = new UIService();

            const register = new Register(
                request
                , routes
                , uiService
            );
            register.setUpClickListener();
            register.setup();

        }

    }
})();
$(document).ready(async () => {
    await Keestash.Register.init();
});
