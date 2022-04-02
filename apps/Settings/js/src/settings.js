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

import store from "../../../../lib/js/src/Store/store";
import Organization from "./GeneralApi/Organization/Organization";
import Users from "./Users/Users";
import {createApp} from "vue";
import i18n from "../../../ForgotPassword/js/src/i18n";

window.addEventListener(
    'DOMContentLoaded'
    , () => {
        bootstrap("users");
    }
);

function bootstrap(id) {
    let app = null;
    if (id === "organizations") {
        app = Organization;
    } else if (id === "users") {
        app = Users;
    }

    createApp(app)
        .use(store)
        .use(i18n)
        .mount("#settings-app");

}

// TODO
// Keestash.Main.setAppNavigationListener(
//     (id) => {
//         bootstrap(id)
//     }
// );