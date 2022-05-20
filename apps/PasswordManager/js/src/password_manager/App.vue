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
              <IsLoading class="pwm-app-is-loading"></IsLoading>
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
            <NoNodeSelected :visible="getSelected() === null"></NoNodeSelected>
            <EdgeDetail v-if="getSelected() !== null"></EdgeDetail>
          </div>
        </div>
      </div>
    </div>
    <Modal
        :open="newEdgeModalOpen"
        :has-description="true"
        @closed="onEdgeModalClose"
        @saved="onEdgeAdd"
        unique-id="password-manager-new-edge-modal"
    >
      <template v-slot:body-description v-if="addEdge.type === 'pwm__new__password'">
        {{ $t('actionBar.credential.description') }}
      </template>
      <template v-slot:body-description v-else>
        {{ $t('actionBar.folder.description') }}
      </template>
      <template v-slot:title v-if="addEdge.type === 'pwm__new__password'">{{
          $t('actionBar.credential.title')
        }}
      </template>
      <template v-slot:title v-else>{{
          $t('actionBar.folder.title')
        }}
      </template>
      <template v-slot:body v-if="addEdge.type === 'pwm__new__password'">
        <form>
          <div class="form-group">
            <label for="edge-name" class="col-form-label">{{
                $t('actionBar.credential.name.label')
              }}</label>
            <input type="text" class="form-control" :placeholder="$t('actionBar.credential.name.placeholder')"
                   v-model="addEdge.form.name"
                   autocomplete="off" required id="edge-name">
          </div>
          <div class="form-group">
            <label for="edge-username" class="col-form-label">{{ $t('actionBar.credential.username.label') }}</label>
            <input type="text" class="form-control" id="edge-username" v-model="addEdge.form.username"
                   :placeholder="$t('actionBar.credential.username.placeholder')">
          </div>
          <div class="form-group">
            <label for="edge-password" class="col-form-label">{{ $t('actionBar.credential.password.label') }}</label>
            <input type="text" class="form-control" id="edge-password" v-model="addEdge.form.password.value"
                   :placeholder="$t('actionBar.credential.password.placeholder')">
          </div>
          <div class="form-group">
            <label for="edge-url" class="col-form-label">{{ $t('actionBar.credential.url.label') }}</label>
            <input type="text" class="form-control" id="edge-url" v-model="addEdge.form.url"
                   :placeholder="$t('actionBar.credential.url.placeholder')">
          </div>
        </form>
      </template>
      <template v-slot:body v-else>
        <form>
          <div class="form-group">
            <label for="edge-name" class="col-form-label">{{
                $t('actionBar.credential.name.label')
              }}</label>
            <input type="text" class="form-control" :placeholder="$t('actionBar.credential.name.placeholder')"
                   v-model="addEdge.form.name"
                   autocomplete="off" required id="edge-name">
          </div>
        </form>
      </template>
      <template v-slot:button-text v-if="addEdge.type === 'pwm__new__password'">
        {{ $t('actionBar.credential.buttonText') }}
      </template>
      <template v-slot:button-text v-else>
        {{ $t('actionBar.folder.buttonText') }}
      </template>
      <template v-slot:negative-button-text v-if="addEdge.type === 'pwm__new__password'">
        {{ $t('actionBar.credential.negativeButtonText') }}
      </template>
      <template v-slot:negative-button-text v-else>{{ $t('actionBar.folder.negativeButtonText') }}</template>
    </Modal>
  </div>
</template>

<script>
import Edge from "./Node/Edge";
import {APP_STORAGE, AXIOS, StartUp} from "../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../config/routes/index";
import NoNodeSelected from "./Node/NoNodeSelected";
import EdgeDetail from "./Node/EdgeDetail/EdgeDetail";
import {Skeleton} from "vue-loading-skeleton";
import {
  EVENT_NAME_ACTION_BAR_ITEM_CLICKED,
  EVENT_NAME_APP_NAVIGATION_CLICKED,
  EVENT_NAME_GLOBAL_SEARCH
} from "../../../../../lib/js/src/base";
import NoEdges from "./Node/NoEdges";
import axios from "axios/index";
import Modal from "../../../../../lib/js/src/Components/Modal";
import IsLoading from "../../../../../lib/js/src/Components/IsLoading";

export const NODE_ID_ROOT = "root";
export const STORAGE_ID_ROOT = "root.id.storage";

export default {
  name: "App",
  components: {IsLoading, Modal, NoEdges, EdgeDetail, NoNodeSelected, Edge, Skeleton},
  data: function () {
    return {
      newEdgeModalOpen: false,
      query: null,
      breadCrumbs: [],
      container: null,
      axios: null,
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
          url: ''
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
    this.appStorage = this.container.query(APP_STORAGE);
    const self = this;

    this.loadEdge(
        self,
        (this.getSelected() || {}).id || NODE_ID_ROOT
    );

    document.addEventListener(
        EVENT_NAME_APP_NAVIGATION_CLICKED
        , function (data) {
          self.loadEdge(self, data.detail.dataset.type);
        });

    document.addEventListener(
        EVENT_NAME_ACTION_BAR_ITEM_CLICKED
        , (e) => {
          e.stopImmediatePropagation();
          self.addEdge.type = e.detail.target.id;
          self.newEdgeModalOpen = true;
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
    getSelected() {
      return this.$store.getters.selectedEdge;
    },
    onSearch(val, edge) {
      if (edge === null || typeof edge === 'undefined') return true;
      if (val === "" || val === null) return true;
      return (edge.node.name.toLowerCase().includes(val.toLowerCase()));
    },
    onEdgeModalClose() {
      this.newEdgeModalOpen = false;
      this.resetForm();
    },
    onEdgeAdd: function () {
      let route = ROUTES.getPasswordManagerFolderCreate();
      if (this.addEdge.type === 'pwm__new__password') {
        route = ROUTES.getPasswordManagerCreate();
      }

      const data = this.addEdge.form;
      data.parent = (this.getSelected() || {}).id || NODE_ID_ROOT;

      this.axios.post(
          route
          , data
      )
          .then((response) => {
            const data = response.data;
            this.$store.dispatch(
                "addEdge"
                , data.edge
            );
            this.resetForm();
          });
    },
    resetForm: function () {
      this.addEdge.form.name = '';
      this.addEdge.form.username = '';
      this.addEdge.form.password.value = '';
      this.addEdge.form.password.passwordClass = 'new-pw-neutral';
      this.addEdge.form.password.hint = '';
      this.addEdge.form.url = '';
    },
    onBreadCrumbClick: function (rootId) {
      this.$store.dispatch("selectEdge", null);
      this.loadEdge(this, rootId);
    },
    loadEdge: function (self, rootId) {
      self = this;
      this.$store.dispatch("selectEdge", null);
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
        this.$store.dispatch("selectEdge", null);
        this.loadEdge(this, edge.node.id);
        return;
      }
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
      this.state = 2;
      this.$store.dispatch("setEdges", node.edges.content);
    }
  }
}
</script>

<style scoped lang="scss">
.pwm-app-is-loading {
  width: 5rem;
  height: 1.5rem;
}
</style>