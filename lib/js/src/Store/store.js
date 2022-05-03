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
import {createStore} from 'vuex';
import _ from "lodash"
import {STORE as PasswordManagerStore} from "../../../../apps/PasswordManager/js/config/vuex/index";
import {STORE as AppsStore} from "../../../../apps/Apps/js/config/vuex/index";

// TODO: maybe, use glob in order to make this some kind of magic
const stores = [
    PasswordManagerStore
    , AppsStore
];

const getDefaultState = () => {
    return {
        templates: [],
        strings: [],
        apiCredentials: {},
        i18n: {}
    };
};

const globalStore = {
    strict: true,
    plugins: [],
    state: getDefaultState(),
    getters: {
        getTemplates: state => {
            return state.templates;
        },
        getStrings: state => {
            return state.strings;
        },
        getApiCredentials: state => {
            return state.apiCredentials;
        }
    },
    mutations: {
        SET_TEMPLATES: (state, templates) => {
            state.templates = templates;
        },
        SET_STRINGS: (state, strings) => {
            state.strings = strings;
        },
        SET_API_CREDENTIALS: (state, apiCredentials) => {
            state.apiCredentials = apiCredentials;
        },
        SET_I18N: (state, i18n) => {
            state.i18n = i18n;
        },
        RESET: state => {
            Object.assign(state, getDefaultState());
        },
    },
    actions: {
        setAssets: ({commit, dispatch}, {templates, strings}) => {
            commit('SET_TEMPLATES', templates);
            commit('SET_STRINGS', strings);
        },
        setApiCredentials: ({commit, dispatch}, {credentials}) => {
            commit('SET_API_CREDENTIALS', credentials);
        },
        setI18n: ({commit, dispatch}, {i18n}) => {
            commit('SET_I18N', i18n);
        },
        reset: ({commit}) => {
            commit('RESET', '');
        },
    }

};

for (let i = 0; i < stores.length; i++) {
    globalStore.state = _.merge(
        globalStore.state || {}
        , stores[i].state || {}
    );
    globalStore.mutations = _.merge(
        globalStore.mutations || {}
        , stores[i].mutations || {}
    );
    globalStore.actions = _.merge(
        globalStore.actions || {}
        , stores[i].actions || {}
    );
    globalStore.getters = _.merge(
        globalStore.getters || {}
        , stores[i].getters || {}
    );
}


// const store = createStore({
//     strict: true,
//     plugins: [],
//     state: getDefaultState(),
//     getters: {
//         getTemplates: state => {
//             return state.templates;
//         },
//         getStrings: state => {
//             return state.strings;
//         },
//         getApiCredentials: state => {
//             return state.apiCredentials;
//         }
//     },
//     mutations: {
//         SET_TEMPLATES: (state, templates) => {
//             state.templates = templates;
//         },
//         SET_STRINGS: (state, strings) => {
//             state.strings = strings;
//         },
//         SET_API_CREDENTIALS: (state, apiCredentials) => {
//             state.apiCredentials = apiCredentials;
//         },
//         SET_I18N: (state, i18n) => {
//             state.i18n = i18n;
//         },
//         RESET: state => {
//             Object.assign(state, getDefaultState());
//         },
//     },
//     actions: {
//         setAssets: ({commit, dispatch}, {templates, strings}) => {
//             commit('SET_TEMPLATES', templates);
//             commit('SET_STRINGS', strings);
//         },
//         setApiCredentials: ({commit, dispatch}, {credentials}) => {
//             commit('SET_API_CREDENTIALS', credentials);
//         },
//         setI18n: ({commit, dispatch}, {i18n}) => {
//             commit('SET_I18N', i18n);
//         },
//         reset: ({commit}) => {
//             commit('RESET', '');
//         },
//     }
// });

export default createStore(globalStore);
