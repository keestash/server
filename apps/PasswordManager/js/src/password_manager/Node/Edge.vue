<template>
    <div class="pwm__row container-fluid pt-1 pb-1" @click="$emit('wasClicked')">
        <div class="row align-items-center">
            <div class="col-6 col-md-2 h2 m-0 d-flex">
                <b-img
                        :src="this.imageUrlPassword"
                        fluid
                        :alt="edge.node.name"
                        v-if="edge.node.type === 'credential'"
                        class="flex-grow-1 flex-shrink-0 node-logo-color"
                ></b-img>

                <b-img
                        :src="this.imageUrl"
                        fluid
                        :alt="edge.node.name"
                        v-else-if="edge.node.type === 'folder' && isOwner"
                        class="flex-grow-1 flex-shrink-0 node-logo-color"
                ></b-img>

                <b-img
                        :src="this.imageUrlShared"
                        fluid
                        :alt="edge.node.name"
                        v-else-if="edge.node.type === 'folder' && !isOwner"
                        class="flex-grow-1 flex-shrink-0 node-logo-color"
                ></b-img>
                <p class="h6 align-self-center" v-if="edge.type === 'share' || edge.type === 'organization'">
                    <b-icon-share-fill :title="this.showOwnerName()"
                                       v-if="edge.type === 'organization'"></b-icon-share-fill>
                    <b-icon-share :title="this.showOwnerName()" v-if="edge.type === 'share'"></b-icon-share>
                </p>
            </div>
            <div class="col flex-grow-1 cropped" :title="edge.node.name">
                {{ edge.node.name }}
            </div>
            <div id="contextMenu" class="col-md-1 justify-content-end">
                <i class="fas fa-ellipsis-h"
                   v-on:click.stop="clickModal"
                ></i>
            </div>
        </div>

        <context-menu id="context-menu" class="row"
                      :ref="'ctxMenu' + this.edge.node.id"
        >
            <div class="col context-menu-col" @click="openModalClick" v-if="this.edge.node.organization === null">
                {{ $t('edge.selection.addToOrganization') }}
            </div>
            <div class="col context-menu-col" @click="removeNode">
                {{ $t('edge.selection.remove') }}
            </div>
        </context-menu>

        <SelectableListModal
                :ref-id="'modal' + this.edge.node.id"
                @onSubmit="submitOrganization"
                @onOpen="openModal"
                :options="organizations"
                :loading="organizationsLoading"
                :no-data-text="$t('edge.contextMenu.addToOrganization.noOrganizationsAvailable')"
                :modal-title="$t('edge.contextMenu.addToOrganization.title')"
                :description="$t('edge.contextMenu.addToOrganization.description')"
        ></SelectableListModal>

    </div>

</template>

<script>
import {
    APP_STORAGE,
    AXIOS,
    DATE_TIME_SERVICE,
    StartUp,
    SYSTEM_SERVICE_GLOBAL
} from "../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../lib/js/src/DI/Container";
import contextMenu from 'vue-context-menu'
import {ROUTES} from "../../config/routes";
import {RESPONSE_CODE_OK, RESPONSE_FIELD_MESSAGES} from "../../../../../../lib/js/src/Backend/Axios";
import SelectableListModal from "../Component/Modal/SelectableListModal";

export default {
    name: "Edge",
    components: {SelectableListModal, contextMenu},
    props: {
        edge: null
    },
    data() {
        return {
            container: {
                container: null,
                services: {
                    axios: null,
                    appStorage: null,
                    dateTimeService: null,
                    systemService: null,
                },
            },
            organizations: [],
            selectedOrganization: null,
            imageUrl: '',
            imageUrlShared: '',
            imageUrlPassword: '',
            organizationsLoading: true
        }
    },
    computed: {
        isOwner: function () {
            const userHash = this.container.services.appStorage.getUserHash();
            if (this.edge.node.user.hash === userHash) return true;

            for (let i = 0; i < this.edge.node.shared_to.content.length; i++) {
                const share = this.edge.node.shared_to.content[i];
                if (userHash === share.user.hash) return false;
            }
            return true;
        },
    },
    created: function () {
        const startUp = new StartUp(
            new Container()
        );
        startUp.setUp();

        this.container.container = startUp.getContainer();
        this.container.services.axios = this.container.container.query(AXIOS);
        this.container.services.dateTimeService = this.container.container.query(DATE_TIME_SERVICE);
        this.container.services.appStorage = this.container.container.query(APP_STORAGE);
        this.container.services.systemService = this.container.container.query(SYSTEM_SERVICE_GLOBAL);

        const url = this.container.services.systemService.getHost().replace('index.php', '');
        this.imageUrl = url + 'asset/svg/folder.svg';
        this.imageUrlShared = url + 'asset/svg/folder-shared.svg';
        this.imageUrlPassword = url + 'asset/svg/password.svg';
    },
    methods: {
        formatDate: function (date) {
            return this.container.services.dateTimeService.format(date);
        },
        openModal: function () {
            this.organizationsLoading = true;
            this.container.services.axios.get(
                ROUTES.getAllOrganizations(
                    this.container.services.appStorage.getUserHash(),
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

                        organizations.push(
                            {
                                value: organization.id
                                , text: organization.name
                            }
                        )
                    }
                    this.organizationsLoading = false;
                    this.organizations = organizations;
                });
        },
        resetModal: function () {
            this.selectedOrganization = null;
        },
        refName: function () {
            return 'ctxMenu' + this.edge.node.id;
        },
        clickModal: function () {
            this.$refs[this.refName()].open();
        },
        removeNode: function () {
            this.container.services.axios.delete(
                ROUTES.getNodeDelete(),
                {
                    node_id: this.edge.node.id
                }
            ).then(
                (r) => {
                    // TODO
                }
            )
            ;
        },
        openModalClick: function () {
            this.$emit('onOpenModalClick', 'modal' + this.edge.node.id);
        },
        showOwnerName: function () {
            if (this.edge.type === 'organization') {
                return 'Shared by ' + this.edge.node.user.name + ' with ' + this.edge.node.organization.name;
            }
            if (this.edge.type === 'share') {
                return 'Shared by ' + this.edge.node.user.name + ' with you';
            }
        },
        submitOrganization: function (selected) {
            this.container.services.axios.httpPut(
                ROUTES.getOrganizationsAddNode(),
                {
                    node_id: this.edge.node.id
                    , organization_id: selected
                }
            ).then(
                (r) => {
                    this.$store.dispatch(
                        'updateSelectedNode'
                        , {
                            organization: r.data.organization
                        }
                    );

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

<style scoped>

</style>