import Vue from "vue";
import Vuex from "vuex";

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
        SET_COMMENTS(state, comments) {
            state.comments = comments;
        },
        ADD_COMMENT(state, comment) {
            state.comments.push(comment)
        },
        SELECT_EDGE(state, edge) {
            state.selectedEdge = edge;
        },
        SET_ATTACHMENTS(state, attachments) {
            state.selectedEdge.node.attachments = attachments;
        },
        ADD_ATTACHMENTS(state, attachments) {
            state.selectedEdge.node.attachments = state.selectedEdge.node.attachments.concat(attachments);
        },
        ADD_ATTACHMENT(state, attachment) {
            state.selectedEdge.node.attachments.push(attachment);
        },
        UPDATE_EDGE(state, edge) {
            let store_p = state.edges.find(p => p.id === edge.id);
            if (store_p !== null) {
                store_p = edge;
            }
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
        setAttachments(context, attachments) {
            context.commit("SET_ATTACHMENTS", attachments);
        },
        addAttachment(context, attachment) {
            context.commit("ADD_ATTACHMENT", attachment);
        },
        addAttachments(context, attachments) {
            context.commit("ADD_ATTACHMENTS", attachments);
        },
        setComments(context, comments) {
            context.commit("SET_COMMENTS", comments);
        },
        addComment(context, comment) {
            context.commit("ADD_COMMENT", comment);
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