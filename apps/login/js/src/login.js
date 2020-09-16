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
import {Routes} from "./Routes/Routes";
import {Submit} from "./Login/Submit/Submit";
import {
    APP_STORAGE,
    BUTTON_SERVICE,
    GLOBAL_ROUTES,
    INPUT_SERVICE,
    MINI_MODAL,
    REQUEST,
    ROUTER,
    TEMPLATE_LOADER
} from "../../../../lib/js/src/StartUp";

(async () => {
    if (!Keestash.Login) {
        Keestash.Login = {};
    }

    Keestash.Login = {

        init: () => {

            const diContainer = Keestash.Main.getContainer();
            const submit = new Submit(
                diContainer.query(ROUTER)
                , diContainer.query(REQUEST)
                , diContainer.query(APP_STORAGE)
                , new Routes()
                , diContainer.query(GLOBAL_ROUTES)
                , diContainer.query(TEMPLATE_LOADER)
                , diContainer.query(INPUT_SERVICE)
                , diContainer.query(BUTTON_SERVICE)
                , diContainer.query(MINI_MODAL)
            );
            submit.handle();
        }
    }
})();

$(document).ready(async () => {
    await Keestash.Login.init();
});

