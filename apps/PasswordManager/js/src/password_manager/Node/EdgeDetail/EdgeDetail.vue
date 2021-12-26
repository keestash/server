<template>
    <div id="pwm__detail__part">
        <div class="col d-flex flex-column">
            <div class="col">
                <div class="row mt-4">
                    <div class="col-md-1 align-self-center">
                        <b-img
                                :src="this.imageUrlPassword"
                                fluid
                                :alt="this.edge.node.name"
                                v-if="edge.node.type === 'credential'"
                                class="flex-grow-1 flex-shrink-0 node-logo-color"
                        ></b-img>

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
                    <div class="col text-center" v-if="saving">
                        <b-skeleton class="float-md-right" type="avatar"></b-skeleton>
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
                                v-b-modal.modal-remove-organization
                        >
                            x
                        </span>
                    </div>
                    <div class="col" v-else>
                        <b-skeleton width="5%"></b-skeleton>
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
                                   :value="this.password"
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
                            <div class="input-group-append" id="pwm__url__button" v-b-modal.external-link-modal>
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
        <b-modal ref="external-link-modal-ref" id="external-link-modal" hide-header hide-footer>
            <h3 id="modal-title">{{ $t('credential.url.external.title') }}</h3>
            <div id="modal-body">
                <p>{{ $t('credential.url.external.text') }}</p>
                <p>{{ $t('credential.url.external.text_info') }} </p>
                <b-alert show variant="light">
                    {{ this.edge.node.url.plain }}
                </b-alert>

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
        </b-modal>

        <SelectableListModal
                ref-id="modal-update-organization"
                @onSubmit="updateOrganization"
                :options="organization.list"
                :loading="organization.loading"
                :no-data-text="$t('credential.detail.organization.addToOrganization.noOrganizationsAvailable')"
                :modal-title="$t('credential.detail.organization.addToOrganization.title')"
                :description="$t('credential.detail.organization.addToOrganization.description')"
        ></SelectableListModal>

        <b-modal
                id="modal-remove-organization"
                ref="modal"
                title="Remove Organization"
                @ok="removeOrganization"
        >
            <form ref="form" @submit.stop.prevent="removeOrganization">
                <div class="h6">Do you really want to remove the node from the organization?</div>
            </form>
        </b-modal>
    </div>

</template>

<script>
import {RESPONSE_CODE_OK} from "../../../../../../../lib/js/src/Backend/Request";
import {APP_STORAGE, AXIOS, StartUp, SYSTEM_SERVICE_GLOBAL, URL_SERVICE} from "../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../../config/routes";
import {RESPONSE_FIELD_MESSAGES} from "../../../../../../../lib/js/src/Backend/Axios";
import Tab from "./Tab";
import {mapState} from "vuex";
import moment from "moment";
import _ from "lodash";
import {BSkeleton} from 'bootstrap-vue';
import {SystemService} from "../../../Service/SystemService";
import SelectableListModal from "../../Component/Modal/SelectableListModal";

export default {
    name: "EdgeDetail",
    components: {SelectableListModal, Tab, BSkeleton},
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
                visible: false
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
    created() {
        this.password = this.edge.node.password.placeholder;

        const startUp = new StartUp(
            new Container()
        );
        startUp.setUp();

        this.container = startUp.getContainer();
        this.axios = this.container.query(AXIOS);
        this.systemService = this.container.query(SYSTEM_SERVICE_GLOBAL);
        this.appStorage = this.container.query(APP_STORAGE);

        const url = this.systemService.getHost().replace('index.php', '');
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
            this.$refs['external-link-modal-ref'].hide()
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
            this.$refs['external-link-modal-ref'].hide();
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
                this.saving = false;
                this.updatePassword(
                    this.passwordField
                    , false
                );
                return;
            }

            axios.request(
                ROUTES.getCredential(
                    this.edge.node.id
                )
            )
                .then((data) => {
                    this.updatePassword(
                        data.decrypted
                        , true
                    );
                })

        },
        updatePassword(password, visible) {
            this.password = password;
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