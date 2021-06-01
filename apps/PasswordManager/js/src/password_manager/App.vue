<template>
  <div class="row no-gutters">
    <div class="col-sm">
      <div class="ks-border-bottom" id="breadcrumb-wrapper">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb" id="breadcrumb">
            <li v-for="breadCrumb in breadCrumbs" :key="breadCrumb.id" class="breadcrumb-item" @click="loadEdge(breadCrumb.id)">
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
            <div class="d-flex justify-content-between align-items-start flex-grow-1 b-b coll flex-column">
              <div class="container-fluid">
                <template v-if="state !== 1">
                  <Edge
                      v-for="edge in edges"
                      :key="edge.id"
                      :edge="edge"
                      @wasClicked="selectRow(edge)"
                      class="pl-0 pr-0"
                  ></Edge>
                </template>
                <Skeleton :count=15 height="25px" v-else/>
                <NoEdges :visible="state === 2 && edges.length === 0"></NoEdges>
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
import {EVENT_NAME_APP_NAVIGATION_CLICKED} from "../../../../../lib/js/src/base";
import NoEdges from "./Node/NoEdges";

export const NODE_ID_ROOT = "root";
export const STORAGE_ID_ROOT = "root.id.storage";

export default {
  name: "App",
  components: {NoEdges, EdgeDetail, NoNodeSelected, Edge, Skeleton},
  data: function () {
    return {
      breadCrumbs: [],
      selected: null,
      container: null,
      axios: null,
      temporaryStorage: null,
      noData: true,
      state: 1
    }
  },
  created: function () {
    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();

    this.container = startUp.getContainer();
    this.axios = this.container.query(AXIOS);
    this.temporaryStorage = this.container.query(TEMPORARY_STORAGE);
    this.loadEdge(
        this.temporaryStorage.get(
            STORAGE_ID_ROOT
            , NODE_ID_ROOT
        )
    );
    const _this = this;

    document.addEventListener(
        EVENT_NAME_APP_NAVIGATION_CLICKED
        , function (data) {
          _this.loadEdge(data.detail.dataset.type);
        });
  },
  computed: {
    noEdgeSelectedVisible: function () {
      return this.selected === null;
    },
    edges: function () {
      return this.$store.getters.edges;
    }
  },
  methods: {
    loadEdge: function (rootId) {
      this.state = 1;
      const _this = this;

      this.axios.request(
          ROUTES.getNode(rootId)
      ).then(function (response) {
        if (RESPONSE_CODE_OK in response.data) {
          return response.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES];
        }
        return [];
      })
          .then(function (data) {
            _this.parseBreadCrumb(data.breadCrumb)
            _this.parseEdges(data.node);
          })
      ;

    },
    selectRow: function (edge) {
      if (edge.node.type === 'folder') {
        this.selected = null;
        this.loadEdge(edge.node.id);
        return;
      }
      this.selected = edge;
      this.$store.dispatch("selectEdge", edge);
    },
    parseBreadCrumb: function (breadCrumbs) {
      this.breadCrumbs = [];
      for (let index in breadCrumbs) {
        if (breadCrumbs.hasOwnProperty(index)) {
          this.breadCrumbs.push(breadCrumbs[index])
        }
      }
    },
    parseEdges: function (node) {
      this.temporaryStorage.set(
          STORAGE_ID_ROOT
          , node.id
      );
      this.state = 2;
      this.$store.dispatch("setEdges", node.edges.content);
    }
  }
}
</script>

<style scoped>

</style>