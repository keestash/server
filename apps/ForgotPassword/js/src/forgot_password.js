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
import Vue from "vue";
import BootstrapVue, {IconsPlugin} from "bootstrap-vue";
import Vuex from "vuex";
import App from "./ForgotPassword/App";
import i18n from "./i18n";
import store from "../../../../lib/js/src/Store/store";

window.addEventListener(
    'DOMContentLoaded'
    , async () => {
        const vueConfig = {
            store,
            i18n,
            render: h => h(App)
        };

        Vue.use(BootstrapVue);
        Vue.use(IconsPlugin);
        Vue.use(Vuex);
        new Vue(
            vueConfig
        )
            .$mount("#forgot_password");

    }
);
