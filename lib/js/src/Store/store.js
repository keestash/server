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
import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

const getDefaultState = () => {
    return {
        templates: [],
        strings: [],
    };
};

const store = new Vuex.Store({
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
    },
    mutations: {
        SET_TEMPLATES: (state, templates) => {
            state.templates = templates;
        },
        SET_STRINGS: (state, strings) => {
            state.strings = strings;
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
        reset: ({commit}) => {
            commit('RESET', '');
        },
    }
});

export default store;
