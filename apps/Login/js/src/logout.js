/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

import {Logout} from "./Logout/Logout";
import {APP_STORAGE, ROUTER, StartUp, TEMPORARY_STORAGE} from "../../../../lib/js/src/StartUp";
import {Container} from "../../../../lib/js/src/DI/Container";

window.addEventListener(
    'DOMContentLoaded'
    , bootstrap
);

function bootstrap() {
    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();
    const diContainer = startUp.getContainer();

    const logout = new Logout(
        diContainer.query(APP_STORAGE)
        , diContainer.query(ROUTER)
        , diContainer.query(TEMPORARY_STORAGE)
    );
    logout.init();
}
