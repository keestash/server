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
import {ButtonService} from "../../../../lib/js/src/UI/Button/ButtonService";
import {InputService} from "../../../../lib/js/src/UI/Input/InputService";
import {Request} from "../../../../lib/js/src/Backend/Request";
import {ConsoleLogger} from "../../../../lib/js/src/Log/ConsoleLogger";
import {AppStorage} from "../../../../lib/js/src/Storage/AppStorage";
import {Router} from "../../../../lib/js/src/Route/Router";
import {Host} from "../../../../lib/js/src/Backend/Host";
import {Mini} from "../../../../lib/js/src/UI/Modal/Mini";
import {TemplateLoader} from "../../../../lib/js/src/Storage/TemplateStorage/TemplateLoader";
import {Routes as GlobalRoutes} from "../../../../lib/js/src/Route/Routes";
import {Parser} from "../../../../lib/js/src/UI/Template/Parser/Parser";
import {ResetPassword} from "./ResetPassword/ResetPassword";
import {Routes} from "./Public/Routes";

(function () {

    if (!Keestash.ForgotPassword) {
        Keestash.ForgotPassword = {};
    }
    if (!Keestash.ForgotPassword.ResetPassword) {
        Keestash.ForgotPassword.ResetPassword = {};
    }
    Keestash.ForgotPassword.ResetPassword = {

        init: async () => {
            const buttonService = new ButtonService();
            const inputService = new InputService();
            const host = new Host();
            const globalRoutes = new GlobalRoutes(
                host
            );
            const request = new Request(
                new ConsoleLogger()
                , new AppStorage()
                , new Router(
                    host
                )
            );
            const routes = new Routes();
            const templateLoader = new TemplateLoader(
                request
                , globalRoutes
            );
            const parser = new Parser();

            const miniModal = new Mini(
                templateLoader
                , parser
            );

            const resetPassword = new ResetPassword(
                buttonService
                , inputService
                , request
                , routes
                , miniModal
            )

            await resetPassword.run();

        }
    }
})();
$(document).ready(async () => {
    await Keestash.ForgotPassword.ResetPassword.init();
});
