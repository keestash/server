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
              <Edge
                  v-for="edge in edges"
                  :key="edge.id"
                  :edge="edge"
                  @wasClicked="selectRow(edge)"
                  class="edge"
              ></Edge>
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

export const NODE_ID_ROOT = "root";
export const STORAGE_ID_ROOT = "root.id.storage";

export default {
  name: "App",
  components: {EdgeDetail, NoNodeSelected, Edge},
  data: () => {
    return {
      breadCrumbs: [],
      selected: null
    }
  },
  created() {
    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();
    const container = startUp.getContainer();
    const temporaryStorage = container.query(TEMPORARY_STORAGE);
    const axios = container.query(AXIOS);

    const rootId = temporaryStorage.get(
        STORAGE_ID_ROOT
        , NODE_ID_ROOT
    );

    axios.request(
        ROUTES.getNode(rootId)
    ).then((response) => {
      if (RESPONSE_CODE_OK in response.data) {
        return response.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES];
      }
      return [];
    })
        .then((data) => {
          this.parseBreadCrumb(data.breadCrumb)
          this.parseEdges(data.node, temporaryStorage);
        })
    ;

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

    selectRow(edge) {
      this.selected = edge;
      this.$store.dispatch("selectEdge", edge);
    },

    parseBreadCrumb(breadCrumbs) {
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

      // for (let index in node.edges.content) {
      //   if (node.edges.content.hasOwnProperty(index)) {
      //     this.edges.push(node.edges.content[index])
      //   }
      // }
      this.$store.dispatch("setEdges", node.edges.content);
    }
  }
}
</script>

<style scoped>

</style>