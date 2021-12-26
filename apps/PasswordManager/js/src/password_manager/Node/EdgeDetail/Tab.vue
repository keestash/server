<template>
    <div>
        <b-tabs content-class="pt-3 border-left border-right border-bottom d-flex flex-column" lazy>
            <b-tab
                    :title="$t('credential.detail.sharesLabel')"
                    active
                    v-if="this.canSeeShareTab()"
            >
                <Share></Share>
            </b-tab>
            <b-tab :title="$t('credential.detail.pwGeneratorLabel')">
                <PasswordGenerator @passwordUsed="passwordUsed"></PasswordGenerator>
            </b-tab>
            <b-tab :title="$t('credential.detail.commentLabel')">
                <Comment></Comment>
            </b-tab>
            <b-tab :title="$t('credential.detail.attachmentsLabel')">
                <div class="tab-pane" id="attachment" role="tabpanel">
                    <Attachments></Attachments>
                </div>
            </b-tab>
        </b-tabs>
    </div>
</template>

<script>
import Share from "./Tab/Share";
import {mapState} from "vuex";
import PasswordGenerator from "./Tab/PasswordGenerator";
import Comment from "./Tab/Comment";
import Attachments from "./Tab/Attachments";
import {APP_STORAGE, StartUp} from "../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../lib/js/src/DI/Container";

export default {
    name: "Tab",
    components: {Attachments, Comment, PasswordGenerator, Share},
    computed: {
        ...mapState({
            edge: function (state) {
                return state.selectedEdge;
            }
        })
    },
    data() {
        return {
            container: {
                container: null,
                services: {
                    storage: null
                }
            }
        }
    },
    created() {
        const startUp = new StartUp(
            new Container()
        );
        startUp.setUp();
        this.container.container = startUp.getContainer();
        this.container.services.appStorage = this.container.container.query(APP_STORAGE);

    },
    methods: {
        passwordUsed(password) {
            this.$emit("passwordUsed", password);
        },
        canSeeShareTab() {
            return this.edge.node.user.hash === this.container.services.appStorage.getUserHash()
                && null === this.edge.node.organization;
        }
    }
}
</script>

<style scoped>

</style>