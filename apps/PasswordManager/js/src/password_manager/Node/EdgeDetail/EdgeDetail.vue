<template>
  <div id="pwm__detail__part">
    <div class="col d-flex flex-column">
      <div class="col">
        <div class="row mt-4">
          <div class="col-md-1 align-self-center">
            <img :src="this.imageUrlPassword" class="img-fluid flex-grow-1 flex-shrink-0 node-logo-color"
                 :alt="this.edge.node.name"
                 v-if="edge.node.type === 'credential'">
          </div>

          <div class="col-md-4 d-flex align-items-center">
            <div class="row">
              <div class="col">
                <input
                    type="text"
                    class="form-control border-0"
                    :value="this.edge.node.name"
                    @blur="onNameChange"
                >
              </div>
            </div>
          </div>
          <div class="d-flex col text-end justify-content-end" v-if="saving">
            <Skeleton class="align-middle" :circle="true" width="40px" height="40px"/>
          </div>
        </div>

        <div
            class="row mb-4"
            v-if="edge.node.organization !== null"
        >
          <div class="col-md-auto">
              <span
                  class="badge bg-info clickable text-white"
                  :title="$t('credential.detail.organization.description')"
              >{{ edge.node.organization.name }}
              </span>
            <span
                class="badge bg-secondary clickable text-white ml-1"
                :title="$t('credential.detail.organization.description')"
                @click="this.removeOrganizationOpened=true"
            >x
            </span>
          </div>

        </div>

        <form id="tab__detail__wrapper">
          <div class="form-group">
            <small class="form-text text-muted">{{ $t('credential.detail.userNameLabel') }}</small>
            <input type="text"
                   class="form-control"
                   :value="this.edge.node.username.plain"
                   @blur="onUsernameChange"
            >
          </div>
          <div class="form-group">
            <small class="form-text text-muted">{{ $t('credential.detail.passwordLabel') }}</small>
            <div class="input-group">
              <input :type="passwordField.visible ? 'text' : 'password'" class="form-control"
                     :readonly="!passwordField.visible"
                     :value="this.passwordField.value"
                     autocomplete="off"
                     @blur="onUpdatePassword"
              >
              <div class="input-group-append" id="pwm__password__eye" @click="loadPassword">
                <div class="input-group-text">
                  <i class="fas fa-eye"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="form-group">
            <small class="form-text text-muted">{{ $t('credential.url.label') }}</small>
            <div class="input-group">
              <input
                  type="url"
                  class="form-control"
                  :placeholder="$t('credential.url.placeholder')"
                  :value="encodeUri(this.edge.node.url.plain)"
                  @blur="onUrlChange"
              >
              <div class="input-group-append"
                   id="pwm__url__button"
                   @click="this.redirectModalOpened=true"
              >
                <div class="input-group-text">
                  <i class="fas fa-external-link-alt"></i>
                </div>
              </div>

            </div>
          </div>

          <Tab @passwordUsed="passwordUsed"></Tab>

        </form>

      </div>

    </div>

    <Modal
        :open="redirectModalOpened"
        :has-description="true"
        :has-negative-button="true"
        :has-positive-button="true"
        unique-id="pwm-redirect-modal"
        @saved="copyToClipBoard"
        @closed="openUrl"
    >
      <template v-slot:title>
        {{ $t('credential.url.external.title') }}
      </template>
      <template v-slot:body-description>
        <p>{{ $t('credential.url.external.text') }}</p>
        <p>{{ $t('credential.url.external.text_info') }} </p>
      </template>
      <template v-slot:body>
        <div class="alert alert-light" role="alert">
          {{ this.edge.node.url.plain }}
        </div>
      </template>
      <template v-slot:button-text>
        <div v-if="!urlField.copyClicked">{{
            $t('credential.url.external.copy')
          }}
        </div>
        <div v-else>
          {{
            $t('credential.url.external.copied')
          }}
        </div>
      </template>
      <template v-slot:negative-button-text>
        {{ $t('credential.url.external.proceed') }}
      </template>
    </Modal>
    <Modal
        :open="removeOrganizationOpened"
        :has-description="false"
        @saved="removeOrganization"
        @closed="this.removeOrganizationOpened=false"
        unique-id="pwm-edge-detail-modal"
        :has-positive-button="true"
        :has-negative-button="true"
    >
      <template v-slot:title>
        {{ $t('edge.contextMenu.remove.modal.title') }}
      </template>
      <template v-slot:button-text>
        {{ $t('edge.contextMenu.remove.modal.positiveButton') }}
      </template>
      <template v-slot:negative-button-text>
        {{ $t('edge.contextMenu.remove.modal.negativeButton') }}
      </template>
      <template v-slot:body>
        {{ $t('edge.contextMenu.remove.modal.body') }}
      </template>
    </Modal>
  </div>

</template>

<script>
import {APP_STORAGE, AXIOS, StartUp, URL_SERVICE} from "../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../../../config/routes/index";
import {RESPONSE_CODE_OK, RESPONSE_FIELD_MESSAGES} from "../../../../../../../lib/js/src/Backend/Axios";
import Tab from "./Tab";
import {mapState} from "vuex";
import moment from "moment";
import _ from "lodash";
import {SystemService} from "../../../Service/SystemService";
import SelectableListModal from "../../Component/Modal/SelectableListModal";
import {Host} from "../../../../../../../lib/js/src/Backend/Host";
import {Skeleton} from "vue-loading-skeleton";
import IsLoading from "../../../../../../../lib/js/src/Components/IsLoading";
import Modal from "../../../../../../../lib/js/src/Components/Modal";

export default {
  name: "EdgeDetail",
  components: {IsLoading, Skeleton, SelectableListModal, Tab, Modal},
  data() {
    return {
      removeOrganizationOpened: false,
      redirectModalOpened: false,
      container: [],
      organization: {
        list: [],
        selected: null,
        loading: false
      },
      saving: false,
      passwordField: {
        visible: false,
        value: ''
      },
      urlField: {
        copyClicked: false
      },
      imageUrlPassword: '',
      axios: null,
      systemService: null,
      appStorage: null
    }
  },
  computed: {
    ...mapState({
      edge: function (state) {
        return state.selectedEdge;
      }
    })
  },
  watch: {
    edge(newEdge, oldEdge) {
      if (null === newEdge) return;
      if (newEdge.id !== oldEdge.id) {
        if (false === this.passwordField.visible) {
          return;
        }
        this.updatePassword(
            this.edge.node.password.placeholder
            , false
        );
        this.saving = false;
      }
    }
  },
  created() {
    this.updatePassword(
        this.edge.node.password.placeholder
        , false
    )

    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();

    this.container = startUp.getContainer();
    this.axios = this.container.query(AXIOS);
    this.appStorage = this.container.query(APP_STORAGE);

    const host = new Host();
    const url = host.getHost().replace('index.php', '');
    this.imageUrlPassword = url + 'asset/svg/password.svg';
  },

  methods: {
    encodeUri(uri) {
      return encodeURI(uri);
    },
    formatDate(date) {
      return moment(date).format();
    },
    copyToClipBoard() {
      this.urlField.copyClicked = true;
      const systemService = new SystemService();
      systemService.copyToClipboard(this.edge.node.url.plain);

      setTimeout(
          () => {
            this.urlField.copyClicked = false;
          },
          3000
      );
    },
    passwordUsed(p) {
      this.updatePasswordRemote(p);
    },

    updateCredential(input) {
      const axios = this.axios;
      this.saving = true;
      _.debounce(
          () => {
            this.saving = true;
            axios.post(
                ROUTES.getPasswordManagerUsersUpdate()
                , input
            ).then(() => {
              this.$store.dispatch(
                  "updateSelectedNode"
                  , input
              );
              this.saving = false;
            })

          }, 500
      )();
    },
    onNameChange(event) {
      event.preventDefault();
      const newName = event.target.value;
      if (newName === this.edge.node.name || newName === "") return;

      this.updateCredential({
        name: newName
        , username: {
          plain: this.edge.node.username.plain
        }
        , url: {
          plain: this.edge.node.url.plain
        }
        , nodeId: this.edge.node.id
      });
    },
    updatePasswordRemote(newPassword) {

      const axios = this.axios;
      this.saving = true;

      _.debounce(
          () => {
            axios.post(
                ROUTES.getPasswordManagerCredentialPasswordUpdate()
                , {
                  passwordPlain: newPassword
                  , nodeId: this.edge.node.id
                }
            ).then(
                (response) => {
                  this.saving = false;
                  if (RESPONSE_CODE_OK in response.data) {
                    return response.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES];
                  }
                  return [];
                }
            ).then((data) => {
            })

          }, 500
      )();

    },
    onUpdatePassword(event) {
      event.preventDefault();
      if (false === this.passwordField.visible) return;
      const newPassword = event.target.value;
      this.updatePasswordRemote(newPassword)
    },
    onUsernameChange(event) {
      const newUserName = event.target.value;
      if (newUserName === this.edge.node.username.plain || newUserName === "") return;
      this.updateCredential({
        name: this.edge.node.name
        , username: {
          plain: newUserName
        }
        , url: {
          plain: this.edge.node.url.plain
        }
        , nodeId: this.edge.node.id
      });
    },
    onUrlChange(event) {
      const newUrl = event.target.value;
      if (newUrl === this.edge.node.url.plain || newUrl === "") return;
      this.updateCredential({
        name: this.edge.node.name
        , username: {
          plain: this.edge.node.username.plain
        }
        , url: {
          plain: newUrl
        }
        , nodeId: this.edge.node.id
      });
    },
    openUrl() {
      const startUp = new StartUp(
          new Container()
      );
      startUp.setUp();
      const container = startUp.getContainer();
      const urlService = container.query(URL_SERVICE);

      if (!urlService.isValidURL(this.edge.node.url.plain)) return;

      window.open(
          encodeURI(this.edge.node.url.plain)
          , '_blank'
      ).focus();
    },
    loadPassword() {
      this.saving = true;
      const startUp = new StartUp(
          new Container()
      );
      startUp.setUp();
      const container = startUp.getContainer();
      const axios = container.query(AXIOS);

      if (true === this.passwordField.visible) {
        this.updatePassword(
            this.edge.node.password.placeholder
            , false
        );
        this.saving = false;
        return;
      }

      axios.request(
          ROUTES.getCredential(
              this.edge.node.id
          )
      )
          .then((data) => {
            this.updatePassword(
                data.data.decrypted
                , true
            );
            this.saving = false;
          })
          .then(() => {
            setTimeout(
                () => {
                  this.updatePassword(
                      this.edge.node.password.placeholder
                      , false
                  );
                  this.saving = false;
                },
                10000
            );
          })

    },
    updatePassword(password, visible) {
      this.passwordField.value = password;
      this.passwordField.visible = visible;
    },
    resetOrganizationModal() {
      this.organization.selected = null;
      this.organization.list = [];
    },
    removeOrganization() {
      this.saving = true;
      this.axios.delete(
          ROUTES.getOrganizationsRemoveNode(),
          {
            node_id: this.edge.node.id
            , organization_id: this.edge.node.organization.id
          }
      ).then(
          (r) => {
            this.$store.dispatch(
                'updateSelectedNode'
                , {
                  organization: null
                }
            );

            this.$store.dispatch(
                'updateSelectedEdge'
                , {
                  type: r.data.type
                }
            )
            this.saving = false;
            this.removeOrganizationOpened = false;
          }
      )
      ;
    },
    updateOrganization(organizationId) {
      this.axios.post(
          ROUTES.getOrganizationsUpdateNode(),
          {
            node_id: this.edge.node.id
            , organization_id: organizationId
          }
      ).then(
          (r) => {
            this.$store.dispatch(
                'updateSelectedNode'
                , {
                  organization: r.data.organization
                }
            )
            this.$store.dispatch(
                'updateSelectedEdge'
                , {
                  type: r.data.type
                }
            )
          }
      )
      ;
    }
  }
}
</script>

<style scoped lang="scss">
.node-edge-detail-is-loading {
  height: 0.25rem;
}

</style>