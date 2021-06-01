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
        },
        SET_EDGES(state, edges) {
            state.edges = edges;
        },
        SELECT_EDGE(state, edge) {
            state.selectedEdge = edge;
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
        updateSelectedNode(context, node) {
            context.commit("UPDATE_SELECTED_NODE", node);
        },
        setSelectedNode(context, node) {
            context.commit("SET_SELECTED_NODE", node);
        },
        setEdges(context, edges) {
            console.log(edges)
            context.commit("SET_EDGES", edges);
        },
        addNodeToEdge(context, node) {
            context.commit('ADD_NODE_TO_EDGE', node);
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