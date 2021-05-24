import Vue from "vue";
import Vuex from "vuex";
import _ from "lodash";

Vue.use(Vuex);

export default new Vuex.Store({
    strict: true,
    state: {
        edges: [],
        selectedEdge: null,
        comments: []
    },
    mutations: {
        ADD_EDGE(state, edge) {
            state.edges.push(edge);
            state.selectedEdge = edge;
        },
        SET_EDGES(state, edges) {
            state.edges = edges;
        },
        SELECT_EDGE(state, edge) {
            state.selectedEdge = edge;
        },
        UPDATE_EDGE(state, edge) {
            let store_p = state.edges.find(p => p.id === edge.id);
            if (store_p !== null) {
                store_p = edge;
            }
        },
        UPDATE_SELECTED_NODE(state, newNode) {
            const currentNode = state.selectedEdge.node;
            state.selectedEdge.node = _.merge(currentNode, newNode);
        },
        SET_SELECTED_NODE(state, newNode) {
            state.selectedEdge.node = newNode;
        }
    },
    actions: {
        addEdge(context, edge) {
            context.commit("ADD_EDGE", edge);
        },
        selectEdge(context, edge) {
            context.commit("SELECT_EDGE", edge);
        },
        updateEdge(context, edge) {
            context.commit("UPDATE_EDGE", edge);
        },
        updateSelectedNode(context, node) {
            context.commit("UPDATE_SELECTED_NODE", node);
        },
        setSelectedNode(context, node) {
            context.commit("SET_SELECTED_NODE", node);
        },
        setEdges(context, edges) {
            context.commit("SET_EDGES", edges);
        }
    },
    getters: {
        edges(state) {
            return state.edges;
        },
        comments(state) {
            return state.comments;
        },
        selectedEdge(state) {
            return state.selectedEdge;
        }
    }
});