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
import {Router} from "../../../../lib/js/src/Route/Router";
import {Request} from "../../../../lib/js/src/Backend/Request";
import {ConsoleLogger} from "../../../../lib/js/src/Log/ConsoleLogger";
import {Routes} from "./Routes/Routes";
import {Submit} from "./Login/Submit/Submit";
import {Routes as GlobalRoutes} from "../../../../lib/js/src/Route/Routes";
import {Host} from "../../../../lib/js/src/Backend/Host";
import {AppStorage} from "../../../../lib/js/src/Storage/AppStorage";
import {TemplateLoader} from "../../../../lib/js/src/Storage/TemplateStorage/TemplateLoader";
import {InputService} from "../../../../lib/js/src/UI/Input/InputService";
import {ButtonService} from "../../../../lib/js/src/UI/Button/ButtonService";
import {Mini} from "../../../../lib/js/src/UI/Modal/Mini";
import {Parser} from "../../../../lib/js/src/UI/Template/Parser/Parser";

(async () => {
    if (!Keestash.Login) {
        Keestash.Login = {};
    }

    Keestash.Login = {

        init: async () => {
            const host = new Host();
            const router = new Router(
                host.getHost()
            );
            const appStorage = new AppStorage();
            const request = new Request(
                new ConsoleLogger()
                , appStorage
                , new Router(
                    host.getHost()
                )
            );
            const globalRoutes = new GlobalRoutes(
                new Host()
            );
            const templateLoader = new TemplateLoader(
                request
                , globalRoutes
            );
            await templateLoader.load(true);
            const templateParser = new Parser();

            const submit = new Submit(
                router
                , request
                , appStorage
                , new Routes()
                , globalRoutes
                , templateLoader
                , new InputService()
                , new ButtonService()
                , new Mini(
                    templateLoader
                    , templateParser
                )
            );
            submit.handle();
        }

    }
})();

$(document).ready(async () => {
    await Keestash.Login.init();
});

