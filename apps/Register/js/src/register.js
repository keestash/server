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
import {Routes} from "./RegisterForm/Public/Routes";
import {AXIOS, LONG_MODAL} from "../../../../lib/js/src/StartUp";

(function () {
    if (!Keestash.Register) {
        Keestash.Register = {};
    }

    Keestash.Register = {

        init: async () => {

            const diContainer = Keestash.Main.getContainer();

            const register = new Register(
                diContainer.query(AXIOS)
                , new Routes()
                , diContainer.query(LONG_MODAL)
            );
            register.setUpClickListener();

        }

    }
})();
$(document).ready(async () => {
    await Keestash.Register.init();
});
