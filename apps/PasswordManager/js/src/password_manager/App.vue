<template>
  <div class="row no-gutters">
    <div class="col-sm">
      <div class="ks-border-bottom" id="breadcrumb-wrapper">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb" id="breadcrumb">
            <li v-for="breadCrumb in this.breadCrumbs" :key="breadCrumb.id" class="breadcrumb-item">
              {{ breadCrumb.name }}
            </li>
          </ol>
        </nav>
      </div>
      <div class="row no-gutters">
        <div class="col-sm-3 node_container">
          <div class="d-flex flex-column">
            <div class="col p-3 b-b">
              <div class="d-flex align-items-center">
                <input
                    :placeholder="$t('searchPasswords')"
                    id="pwm_search_passwords"
                    type="text"
                    class="form-control form-control-sm"
                >
              </div>
            </div>
            <div class="d-flex justify-content-between align-items-start flex-grow-1 b-b coll flex-column"
                 id="pwm__node__container">
              <div class="container">
                <template v-if="state.value === state.states.STATE_LOADED">
                  <Edge
                      v-for="edge in edges"
                      :key="edge.id"
                      :edge="edge"
                      @wasClicked="selectRow(edge)"
                      class="edge"
                  ></Edge>
                </template>
                <Skeleton :count=15 height="25px" v-else/>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-9">
          <div id="pwm__node__detail">
            <NoNodeSelected :visible="selected === null"></NoNodeSelected>
            <EdgeDetail v-if="selected !== null"></EdgeDetail>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Edge from "./Node/Edge";
import {AXIOS, StartUp, TEMPORARY_STORAGE} from "../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../config/routes";
import {RESPONSE_CODE_OK, RESPONSE_FIELD_MESSAGES} from "../../../../../lib/js/src/Backend/Axios";
import NoNodeSelected from "./Node/NoNodeSelected";
import EdgeDetail from "./Node/EdgeDetail/EdgeDetail";
import {Skeleton} from "vue-loading-skeleton";

export const NODE_ID_ROOT = "root";
export const STORAGE_ID_ROOT = "root.id.storage";

const STATE_LOADING = 1;
const STATE_LOADED = 2;

export default {
  name: "App",
  components: {EdgeDetail, NoNodeSelected, Edge, Skeleton},
  data: () => {
    return {
      breadCrumbs: [],
      selected: null,
      container: null,
      axios: null,
      state: {
        value: STATE_LOADING,
        states: {
          STATE_LOADING: STATE_LOADING,
          STATE_LOADED: STATE_LOADED
        }
      }
    }
  },
  created() {
    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();
    this.container = startUp.getContainer();
    this.axios = this.container.query(AXIOS);
    this.loadEdge(NODE_ID_ROOT);
  },
  computed: {
    noEdgeSelectedVisible() {
      return this.selected === null;
    },
    edges() {
      return this.$store.getters.edges;
    }
  },
  methods: {
    loadEdge(rootId){
      const temporaryStorage = this.container.query(TEMPORARY_STORAGE);

      this.axios.request(
          ROUTES.getNode(rootId)
      ).then((response) => {
        if (RESPONSE_CODE_OK in response.data) {
          return response.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES];
        }
        return [];
      })
          .then((data) => {
            this.state.value = this.state.states.STATE_LOADED;
            this.parseBreadCrumb(data.breadCrumb)
            this.parseEdges(data.node, temporaryStorage);
          })
      ;

    },
    selectRow(edge) {
      if (edge. node.type === 'folder') {
        this.loadEdge(edge.node.id);
        return;
      }
      this.selected = edge;
      this.$store.dispatch("selectEdge", edge);
    },

    parseBreadCrumb(breadCrumbs) {
      this.breadCrumbs = [];
      for (let index in breadCrumbs) {
        if (breadCrumbs.hasOwnProperty(index)) {
          this.breadCrumbs.push(breadCrumbs[index])
        }
      }
    },

    parseEdges(node, temporaryStorage) {
      temporaryStorage.set(
          STORAGE_ID_ROOT
          , node.id
      );

      this.$store.dispatch("setEdges", node.edges.content);
    }
  }
}
</script>

<style scoped>

</style>