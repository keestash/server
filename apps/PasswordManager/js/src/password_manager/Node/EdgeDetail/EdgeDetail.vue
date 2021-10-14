<template>
    <div class="row" id="pwm__detail__part">
        <div class="col d-flex flex-column">
            <div class="col">
                <div class="row mt-4 mb-4">
                    <div class="col-md-1 align-self-center">
                        <p class="h1">
                            <b-icon-code-square class="node-logo-color"></b-icon-code-square>
                        </p>
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
                <form id="tab__detail__wrapper">
                    <div class="form-group">
                        <small class="form-text text-muted">{{ $t('credential.detail.userNameLabel') }}</small>
                        <input type="text"
                               class="form-control"
                               :value="this.edge.node.username"
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
                        <small class="form-text text-muted">{{ $t('credential.detail.websiteLabel') }}</small>
                        <div class="input-group">
                            <input
                                    type="url"
                                    class="form-control"
                                    :placeholder="this.edge.node.url"
                                    :value="this.edge.node.url"
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
        <b-modal id="external-link-modal" hide-header hide-footer>
            <h3 id="modal-title">External link warning</h3>
            <div id="modal-body">
                <p>You are about to visit an external link and we would like to pause for a moment out
                    of an abundance of caution.</p>
                <p>You are being redirected to </p>
                <b-alert show variant="light">
                    {{ this.edge.node.url }}
                </b-alert>

                <div class="pull-left">
                    <button class="btn btn-secondary" @click="copyToClipBoard">Copy</button>
                    <button class="btn btn-primary" @click="openUrl">Proceed</button>
                </div>
            </div>
        </b-modal>
    </div>

</template>

<script>
import {RESPONSE_CODE_OK} from "../../../../../../../lib/js/src/Backend/Request";
import {AXIOS, StartUp, URL_SERVICE} from "../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../../config/routes";
import {RESPONSE_FIELD_MESSAGES} from "../../../../../../../lib/js/src/Backend/Axios";
import Tab from "./Tab";
import {mapState} from "vuex";
import moment from "moment";
import _ from "lodash";
import {BSkeleton} from 'bootstrap-vue';
import {SystemService} from "../../../Service/SystemService";

export default {
    name: "EdgeDetail",
    components: {Tab, BSkeleton},
    data() {
        return {
            createdFormatted: null,
            saving: false,
            passwordField: {
                visible: false
            }
        }
    },
    computed: {
        ...mapState({
            edge: function (state) {
                let edge = state.selectedEdge;
                edge.node.url = encodeURI(edge.node.url);
                edge.node.createdFormatted = moment(edge.node.create_ts.date).format();

                return edge;
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

    },

    methods: {
        copyToClipBoard() {
            const systemService = new SystemService();
            systemService.copyToClipboard(this.edge.node.url);
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
                , username: this.edge.node.username
                , url: this.edge.node.url
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
            if (newUserName === this.edge.node.username || newUserName === "") return;
            this.updateCredential({
                name: this.edge.node.name
                , username: newUserName
                , url: this.edge.node.url
                , nodeId: this.edge.node.id
            });
        },
        onUrlChange(event) {
            const newUrl = event.target.value;
            if (newUrl === this.edge.node.url || newUrl === "") return;
            this.updateCredential({
                name: this.edge.node.name
                , username: this.edge.node.username
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
            if (!urlService.isValidURL(this.edge.node.url)) return;

            window.open(
                encodeURI(this.edge.node.url)
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
                .then((response) => {
                    this.saving = false;
                    if (RESPONSE_CODE_OK in response.data) {
                        return response.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES];
                    }
                    return [];
                })
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
    }
}
</script>

<style scoped lang="scss">

</style>