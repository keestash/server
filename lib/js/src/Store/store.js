import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

const getDefaultState = () => {
    return {
        templates: [],
        strings: []
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
        }
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
        }
    },
    actions: {
        setAssets: ({commit, dispatch}, {templates, strings}) => {
            commit('SET_TEMPLATES', templates);
            commit('SET_STRINGS', strings);
        },
        reset: ({commit}) => {
            commit('RESET', '');
        }
    }
});

export default store;
