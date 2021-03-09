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
import {APP_STORAGE, ROUTER} from "../../../../lib/js/src/StartUp";

(function () {
    if (!Keestash.Logout) {
        Keestash.Logout = {};
    }

    Keestash.Logout = {

        init: async () => {
            const diContainer = Keestash.Main.getContainer();

            const logout = new Logout(
                diContainer.query(APP_STORAGE)
                , diContainer.query(ROUTER)
            );
            logout.init();
        }

    }
})();
$(document).ready(async () => {
    await Keestash.Logout.init();
});
