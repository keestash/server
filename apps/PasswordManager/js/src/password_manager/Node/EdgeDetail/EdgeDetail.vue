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
          <div class="col text-right" v-if="saving">
            <PuSkeleton :circle="true" width="30px" height="30px"/>
          </div>
        </div>

        <div
            class="row mb-4"
            v-if="edge.node.organization !== null"
        >
          <div
              class="col"
              v-if="!this.organization.loading"
          >

                        <span
                            class="badge badge-info clickable"
                            :title="$t('credential.detail.organization.description')"
                            @click="openModalClick"
                        >
                            {{ edge.node.organization.name }}
                        </span>
            <span
                class="badge badge-secondary"
                :title="$t('credential.detail.organization.description')"
                data-toggle="modal" data-target="#remove-organization-modal"
            >
                            x
                        </span>
          </div>
          <div class="col" v-else>
            <PuSkeleton width="5%"></PuSkeleton>
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
              <div class="input-group-append" id="pwm__url__button" data-toggle="modal"
                   data-target="#url-redirect-modal">
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

    <div class="modal fade" id="url-redirect-modal" tabindex="-1" role="dialog" aria-labelledby="urlRedirectModalLabel"
         aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-body">
            <h3>{{ $t('credential.url.external.title') }}</h3>
            <div>
              <p>{{ $t('credential.url.external.text') }}</p>
              <p>{{ $t('credential.url.external.text_info') }} </p>

              <div class="alert alert-light" role="alert">
                {{ this.edge.node.url.plain }}
              </div>

              <div class="pull-left">
                <button class="btn btn-secondary" @click="copyToClipBoard">{{
                    $t('credential.url.external.copy')
                  }}
                </button>
                <button class="btn btn-primary" @click="openUrl">{{
                    $t('credential.url.external.proceed')
                  }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <SelectableListModal
        ref-id="modal-update-organization"
        @onSubmit="updateOrganization"
        :options="organization.list"
        :loading="organization.loading"
        :no-data-text="$t('credential.detail.organization.addToOrganization.noOrganizationsAvailable')"
        :modal-title="$t('credential.detail.organization.addToOrganization.title')"
        :description="$t('credential.detail.organization.addToOrganization.description')"
    ></SelectableListModal>

    <div class="modal" tabindex="-1" role="dialog" id="remove-organization-modal">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Remove Organization</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form ref="form" @submit.stop.prevent="removeOrganization">
              <div class="h6">Do you really want to remove the node from the organization?</div>
            </form>
          </div>
        </div>
      </div>
    </div>

  </div>

</template>

<script>
import {APP_STORAGE, AXIOS, StartUp, URL_SERVICE} from "../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../../config/routes";
import {RESPONSE_CODE_OK, RESPONSE_FIELD_MESSAGES} from "../../../../../../../lib/js/src/Backend/Axios";
import Tab from "./Tab";
import {mapState} from "vuex";
import moment from "moment";
import _ from "lodash";
import {SystemService} from "../../../Service/SystemService";
import SelectableListModal from "../../Component/Modal/SelectableListModal";
import {Host} from "../../../../../../../lib/js/src/Backend/Host";

export default {
  name: "EdgeDetail",
  components: {SelectableListModal, Tab},
  data() {
    return {
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
      const systemService = new SystemService();
      systemService.copyToClipboard(this.edge.node.url.plain);
    },
    passwordUsed(p) {
      this.updatePasswordRemote(p);
    },

    updateCredential(input) {
      const axios = this.axios;

      _.debounce(
          () => {
            this.saving = true;
            axios.post(
                ROUTES.getPasswordManagerUsersUpdate()
                , input
            ).then(
                (response) => {
                  this.saving = false;
                  if (RESPONSE_CODE_OK in response.data) {
                    return response.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES];
                  }
                  return [];
                }
            ).then((data) => {
              this.$store.dispatch(
                  "updateSelectedNode"
                  , input
              );
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
        , username: this.edge.node.username.plain
        , url: this.edge.node.url.plain
        , nodeId: this.edge.node.id
      });
    },
    openModalClick: function () {
      this.organizationsLoading = true;
      this.axios.get(
          ROUTES.getAllOrganizations(
              this.appStorage.getUserHash(),
              false
          )
      )
          .then((r) => {
            if (RESPONSE_CODE_OK in r.data) {
              return r.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES];
            }
            return [];
          })
          .then((data) => {

            const organizations = [];

            for (let index in data.organizations) {
              const organization = data.organizations[index];

              if (null !== this.edge.node.organization && this.edge.node.organization.id === organization.id) {
                continue;
              }

              organizations.push(
                  {
                    value: organization.id
                    , text: organization.name
                  }
              )
            }
            this.organization.loading = false;
            this.organization.list = organizations;
            this.$emit('onOpenModalClick', 'modal-update-organization');
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
        , username: newUserName
        , url: this.edge.node.url.plain
        , nodeId: this.edge.node.id
      });
    },
    onUrlChange(event) {
      const newUrl = event.target.value;
      if (newUrl === this.edge.node.url.plain || newUrl === "") return;
      this.updateCredential({
        name: this.edge.node.name
        , username: this.edge.node.username.plain
        , url: newUrl
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

</style>