<template>
  <div class="row g-0">
    <div class="col-sm">
      <div class="row ks-border-bottom p-0" id="breadcrumb-wrapper">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb" id="breadcrumb" v-if="state !== 1">
            <li v-for="breadCrumb in breadCrumbs" :key="breadCrumb.id" class="breadcrumb-item"
                @click="onBreadCrumbClick(breadCrumb.id)">
              {{ breadCrumb.name }}
            </li>
          </ol>
          <ol class="breadcrumb" id="breadcrumb" v-else>
            <li class="breadcrumb-item">
              <Skeleton :count="1" height="25px" width="100px"></Skeleton>
            </li>
          </ol>
        </nav>
      </div>
      <div class="row g-0">
        <div class="col-sm-3 node_container p-0">
          <div class="d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start flex-grow-1 b-b coll flex-column">
              <div class="container-fluid p-0">
                <Edge
                    v-if="state !== 1"
                    v-for="(edge, index) in edges"
                    :key="edge.id"
                    :edge="edge"
                    @wasClicked="selectRow(edge)"
                    @wasDeleted="deleteRow(edge)"
                    v-show="onSearch(query,edge)"
                    :is-first="index === 0"
                ></Edge>
                <div class="container-fluid" v-else>
                  <Skeleton :count=15 height="25px"/>
                </div>
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
      <div class="modal" tabindex="-1" role="dialog" id="new-edge-modal">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Modal title</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <form @submit="onEdgeAdd">
                <div class="form-group">
                  <label for="edge-name">Name</label>
                  <input
                      id="edge-name"
                      v-model="addEdge.form.name"
                      type="text"
                      placeholder="Enter name"
                      autocomplete="off"
                      required
                  >
                  <label><small></small></label>
                </div>

                <div class="form-group" v-if="addEdge.type === 'pwm__new__password'">
                  <label for="edge-username">Username</label>
                  <input
                      id="edge-username"
                      v-model="addEdge.form.username"
                      type="text"
                      placeholder="Enter Username"
                      autocomplete="off">
                  <label><small></small></label>
                </div>

                <div class="form-group" v-if="addEdge.type === 'pwm__new__password'">
                  <label for="edge-password">Password</label>
                  <input
                      id="edge-password"
                      v-model="addEdge.form.password.value"
                      type="password"
                      placeholder="Enter Password"
                      autocomplete="off"
                      @input="checkEntropy"
                  >
                  <label :class="addEdge.form.password.passwordClass">
                    <small>
                      {{ addEdge.form.password.hint }}
                    </small>
                  </label>
                </div>

                <div class="form-group" v-if="addEdge.type === 'pwm__new__password'">
                  <label for="edge-url">URL</label>
                  <input
                      id="edge-url"
                      v-model="addEdge.form.url"
                      type="text"
                      placeholder="Enter URL"
                      autocomplete="off"
                  >
                  <label><small></small></label>
                </div>

                <button type="submit" class="btn btn-primary">Submit</button>
              </form>
            </div>
          </div>
        </div>
      </div>

    </div>

  </div>
</template>

<script>
import Edge from "./Node/Edge";
import {APP_STORAGE, AXIOS, StartUp, TEMPORARY_STORAGE} from "../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../config/routes/index";
import {RESPONSE_CODE_OK, RESPONSE_FIELD_MESSAGES} from "../../../../../lib/js/src/Backend/Axios";
import NoNodeSelected from "./Node/NoNodeSelected";
import EdgeDetail from "./Node/EdgeDetail/EdgeDetail";
import {Skeleton} from "vue-loading-skeleton";
import {
  EVENT_NAME_ACTION_BAR_ITEM_CLICKED,
  EVENT_NAME_APP_NAVIGATION_CLICKED,
  EVENT_NAME_GLOBAL_SEARCH
} from "../../../../../lib/js/src/base";
import NoEdges from "./Node/NoEdges";
import {Modal} from "bootstrap";
import axios from "axios/index";

export const NODE_ID_ROOT = "root";
export const STORAGE_ID_ROOT = "root.id.storage";

export default {
  name: "App",
  components: {NoEdges, EdgeDetail, NoNodeSelected, Edge, Skeleton},
  data: function () {

    return {
      query: null,
      breadCrumbs: [],
      selected: null,
      container: null,
      axios: null,
      temporaryStorage: null,
      noData: true,
      state: 1,
      timer: function () {
      },
      addEdge: {
        type: 1,
        form: {
          name: '',
          username: '',
          password: {
            value: '',
            passwordClass: 'new-pw-neutral',
            hint: ''
          },
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
    this.appStorage = this.container.query(APP_STORAGE);
    const self = this;

    this.loadEdge(
        self,
        this.temporaryStorage.get(
            STORAGE_ID_ROOT
            , NODE_ID_ROOT
        )
    );

    document.addEventListener(
        EVENT_NAME_APP_NAVIGATION_CLICKED
        , function (data) {
          self.selected = null;
          self.loadEdge(self, data.detail.dataset.type);
        });

    document.addEventListener(
        EVENT_NAME_ACTION_BAR_ITEM_CLICKED
        , (e) => {
          e.stopImmediatePropagation();
          const modal = new Modal('#new-edge-modal');
          modal.show();
          self.addEdge.type = e.detail.target.id;
        }
    );

    document.addEventListener(
        EVENT_NAME_GLOBAL_SEARCH
        , function (event) {
          self.query = event.detail;
        }
    )
  },
  computed: {
    edges: function () {
      return this.$store.getters.edges;
    },
  },
  methods: {
    onSearch(val, edge) {
      if (edge === null || typeof edge === 'undefined') return true;
      if (val === "" || val === null) return true;
      return (edge.node.name.toLowerCase().includes(val.toLowerCase()));
    },
    onEdgeAdd: function (e) {
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
            // TODO hide new-edge-modal
          });
    },
    hideModal: function () {
      this.addEdge.form.name = '';
      this.addEdge.form.username = '';
      this.addEdge.form.password.value = '';
      this.addEdge.form.url = '';
      this.addEdge.form.note = '';
      this.addEdge.form.password.value = '';
      this.addEdge.form.password.passwordClass = 'new-pw-neutral';
      this.addEdge.form.password.hint = '';
    },
    onBreadCrumbClick: function (rootId) {
      this.selected = null;
      this.loadEdge(this, rootId);
    },
    loadEdge: function (self, rootId) {
      self = this;
      this.state = 1;

      self.axios.get(
          ROUTES.getNode(rootId)
      ).then(
          function (response) {
            return response.data;
          }
      ).then(
          function (data) {
            self.parseBreadCrumb(data.breadCrumb);
            return data;
          }
      ).then(
          function (data) {
            self.parseEdges(data.node);
          }
      )
    },
    selectRow: function (edge) {
      if (edge.node.type === 'folder') {
        this.selected = null;
        this.loadEdge(this, edge.node.id);
        return;
      }
      this.selected = edge;
      this.$store.dispatch("selectEdge", edge);
    },
    deleteRow: function (edge) {
      this.$store.dispatch('removeEdge', edge);
    },
    parseBreadCrumb: function (breadCrumbs) {
      this.breadCrumbs = [];
      for (let index in breadCrumbs) {
        if (breadCrumbs.hasOwnProperty(index)) {
          this.breadCrumbs.push(breadCrumbs[index]);
        }
      }
    },
    checkEntropy: function () {
      const _this = this;
      const _form = this.addEdge.form;
      const func = function () {

        if (_form.password.value.length === 0) {
          _form.password.passwordClass = 'new-pw-neutral';
          _form.password.hint = _this.$t('credential.newPassword.quality.neutral');
          return;
        }

        if (_form.password.value.length < 5) {
          _form.password.passwordClass = 'new-pw-bad';
          _form.password.hint = _this.$t('credential.newPassword.quality.bad');
          return;
        }

        axios.get(
            ROUTES.getGenerateQuality(
                _form.password.value
            )
        )
            .then((r) => {
              return r.data;
            })
            .then((data) => {

              switch (data.quality || 0) {
                case 1:
                  _form.password.passwordClass = 'new-pw-good';
                  _form.password.hint = _this.$t('credential.newPassword.quality.good');
                  break;
                case 0:
                  _form.password.passwordClass = 'new-pw-weak';
                  _form.password.hint = _this.$t('credential.newPassword.quality.weak');
                  break;
                case -1:
                  _form.password.passwordClass = 'new-pw-bad';
                  _form.password.hint = _this.$t('credential.newPassword.quality.bad');
                  break;
                default:
                  _form.password.passwordClass = 'new-pw-neutral';
                  _form.password.hint = _this.$t('credential.newPassword.quality.neutral');
              }
            });
      }

      clearTimeout(this.timer);
      this.timer = setTimeout(function () {
        func()
      }, 300);

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