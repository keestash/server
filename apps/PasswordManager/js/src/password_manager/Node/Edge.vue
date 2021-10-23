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

            </div>
            <div class="col-6 col-md-6 m-0">
                <div class="col cropped node-title" :title="edge.node.name">
                    <div class="row">
                        <div class="col">
                            <span class="text-color-grey-dark">{{ edge.node.name }}</span>
                            <!--              <br>-->
                            <!--              <div v-if="edge.node.type==='credential'">{{ edge.node.username }}</div>-->
                            <!--              <div v-else>{{ formatDate(edge.node.create_ts.date) }}</div>-->
                        </div>
                    </div>
                </div>
            </div>
            <div id="contextMenu" class="col justify-content-end align-items-center">
                <i class="fas fa-ellipsis-h"
                   v-on:click.stop="$refs.ctxMenu.open"
                ></i>
            </div>
        </div>

        <context-menu id="context-menu" ref="ctxMenu" class="row">
            <div class="col context-menu-col" v-b-modal.modal-prevent-closing>Add To Organization</div>
        </context-menu>

        <b-modal
                id="modal-prevent-closing"
                ref="modal"
                title="Add To Organization"
                @show="openModal"
                @hidden="resetModal"
                @ok="submitOrganization"
        >
            <form ref="form" @submit.stop.prevent="submitOrganization">
                <b-form-select v-model="selectedOrganization" :options="organizations"
                               :select-size="4"></b-form-select>
            </form>
        </b-modal>
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

export default {
    name: "Edge",
    components: {contextMenu},
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
                imageUrl: '',
                imageUrlShared: '',
                imageUrlPassword: '',
            },
            organizations: [],
            selectedOrganization: null
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
        openModal() {
            this.container.services.axios.get(
                ROUTES.getAllOrganizations()
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

                    this.organizations = organizations;
                });
        },
        resetModal() {
            this.selectedOrganization = null;
        },
        submitOrganization() {

            this.container.services.axios.httpPut(
                ROUTES.getOrganizationsUpdateNode(),
                {
                    node_id: this.edge.node.id
                    , organization_id: this.selectedOrganization
                }
            ).then(
                (r) => {
                    this.$store.dispatch(
                        'updateSelectedNode'
                        , {
                            organization: r.data.organization
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