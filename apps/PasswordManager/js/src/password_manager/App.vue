<template>
  <div class="row no-gutters">
    <div class="col-sm">
      <div class="ks-border-bottom" id="breadcrumb-wrapper">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb" id="breadcrumb">
            <li v-for="breadCrumb in breadCrumbs" :key="breadCrumb.id" class="breadcrumb-item"
                @click="onBreadCrumbClick(breadCrumb.id)">
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
              <div class="container-fluid p-0">
                <template v-if="state !== 1">
                  <Edge
                      v-for="edge in edges"
                      :key="edge.id"
                      :edge="edge"
                      @wasClicked="selectRow(edge)"
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

    <div>
      <b-modal ref="new-edge-modal" hide-footer hide-backdrop no-fade @hide="hideModal">
        <b-form @submit="onEdgeAdd">
          <b-form-group
              id="input-group-edge-name"
              label="Name:"
              label-for="edge-name"
              description="The node name"
          >
            <b-form-input
                id="edge-name"
                v-model="addEdge.form.name"
                type="text"
                placeholder="Enter name"
                required
            ></b-form-input>
          </b-form-group>

          <b-form-group
              id="input-group-edge-username"
              label="Username:"
              label-for="edge-username"
              description="The username"
              v-if="addEdge.type === 'pwm__new__password'"
          >
            <b-form-input
                id="edge-username"
                v-model="addEdge.form.username"
                type="text"
                placeholder="Enter Username"
            ></b-form-input>
          </b-form-group>

          <b-form-group
              id="input-group-edge-password"
              label="Password:"
              label-for="edge-password"
              description="The password"
              v-if="addEdge.type === 'pwm__new__password'"
          >
            <b-form-input
                id="edge-password"
                v-model="addEdge.form.password"
                type="text"
                placeholder="Enter Password"
            ></b-form-input>
          </b-form-group>

          <b-form-group
              id="input-group-edge-url"
              label="URL:"
              label-for="edge-url"
              description="The URL"
              v-if="addEdge.type === 'pwm__new__password'"
          >
            <b-form-input
                id="edge-url"
                v-model="addEdge.form.url"
                type="text"
                placeholder="Enter URL"
            ></b-form-input>
          </b-form-group>

          <b-button type="submit" variant="primary">Submit</b-button>
        </b-form>
      </b-modal>
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
import {EVENT_NAME_ACTION_BAR_ITEM_CLICKED, EVENT_NAME_APP_NAVIGATION_CLICKED} from "../../../../../lib/js/src/base";
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
      state: 1,
      addEdge: {
        type: 1,
        form: {
          name: '',
          username: '',
          password: '',
          url: '',
          note: ''
        }
      },
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
          _this.selected = null;
          _this.loadEdge(data.detail.dataset.type);
        });


    document.addEventListener(
        EVENT_NAME_ACTION_BAR_ITEM_CLICKED
        , (e) => {
          e.stopImmediatePropagation();
          this.$refs['new-edge-modal'].show();
          this.addEdge.type = e.detail.target.id;
        }
    )
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
    onEdgeAdd(e) {
      e.preventDefault();
      e.stopImmediatePropagation();
      e.stopPropagation();

      let route = ROUTES.getPasswordManagerFolderCreate();
      if (this.addEdge.type === 'pwm__new__password') {
        route = ROUTES.getPasswordManagerCreate();
      }

      const data = this.addEdge.form;
      data.parent = this.temporaryStorage.get(
          STORAGE_ID_ROOT
          , NODE_ID_ROOT
      );

      this.axios.post(
          route
          , data
      ).then((response) => {

        if (RESPONSE_CODE_OK in response.data) {
          return response.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES];
        }
        return [];
      })
          .then((data) => {
            this.$store.dispatch(
                "addEdge"
                , data.edge
            );
            this.$refs['new-edge-modal'].hide();
          });
    },
    hideModal() {
      this.addEdge.form.name = '';
      this.addEdge.form.username = '';
      this.addEdge.form.password = '';
      this.addEdge.form.url = '';
      this.addEdge.form.note = '';
    },
    onBreadCrumbClick: function (rootId) {
      this.selected = null;
      this.loadEdge(rootId);
    },
    loadEdge: function (rootId) {
      this.state = 1;
      const _this = this;

      this.axios.request(
          ROUTES.getNode(rootId)
      )
          .then(function (response) {
            const data = response.data;
            console.log(data)
            if (data.length === 0) return;

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