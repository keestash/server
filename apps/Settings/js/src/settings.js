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
import i18n from "./../config/i18n/index";
import {EVENT_NAME_APP_NAVIGATION_CLICKED} from "../../../../lib/js/src/base";

let selected = null;
window.addEventListener(
    'DOMContentLoaded'
    , () => {
        bootstrap("settings-users");
        load();
    }
);

function bootstrap(id) {
    if (id === selected) return;
    selected = id;
    let app = null;
    if (id === "settings-organizations") {
        app = Organization;
    } else if (id === "settings-users") {
        app = Users;
    }

    createApp(app)
        .use(store)
        .use(i18n)
        .use(i18n)
        .mount("#settings-app");

}

function load() {
    document.addEventListener(
        EVENT_NAME_APP_NAVIGATION_CLICKED
        , function (data) {
            bootstrap(data.detail.dataset.type);
        });
}
