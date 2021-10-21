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
import Vue from "vue";
import Organization from "./GeneralApi/Organization/Organization";
import BootstrapVue from "bootstrap-vue";
import $ from "jquery";
import Users from "./Users/Users";

(() => {
    if (!Keestash.Apps.Settings) {
        Keestash.Apps.Settings = {};
    }

    Keestash.Apps.Settings = {

        init: () => {

            Keestash.Main.setAppNavigationListener(
                (id) => {
                    bootstrap(id)
                }
            );
            bootstrap("organizations");
        }

    }

    function bootstrap(id) {
        const appContentInner = $("#app-content-inner");
        appContentInner.html("");
        appContentInner.html("<div id='settings-app'></div>");

        let app = null;
        if (id === "organizations") {
            app = Organization;
        } else if (id === "users") {
            app = Users;
        }
        const vueConfig = {
            store,
            render: h => h(app)
        };

        Vue.use(BootstrapVue);
        new Vue(vueConfig)
            .$mount("#settings-app");
    }

})();

$(document).ready(() => {
    Keestash.Apps.Settings.init();
});