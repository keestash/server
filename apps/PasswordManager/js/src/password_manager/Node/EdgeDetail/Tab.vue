<template>
  <div>

    <ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item" role="presentation" v-if="this.canSeeShareTab()">
        <a class="nav-link active" id="home-tab" data-bs-toggle="tab" href="#home" role="tab" aria-controls="home"
           aria-selected="true">{{ $t('credential.detail.sharesLabel') }}</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" id="profile-tab" data-bs-toggle="tab" href="#profile" role="tab" aria-controls="profile"
           aria-selected="false">{{ $t('credential.detail.pwGeneratorLabel') }}</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" id="contact-tab" data-bs-toggle="tab" href="#contact" role="tab" aria-controls="contact"
           aria-selected="false">{{ $t('credential.detail.commentLabel') }}</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" id="contact-tab1" data-bs-toggle="tab" href="#contact1" role="tab" aria-controls="contact"
           aria-selected="false">{{ $t('credential.detail.attachmentsLabel') }}</a>
      </li>
    </ul>
    <div class="tab-content" id="myTabContent">
      <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab"
           v-if="this.canSeeShareTab()">
        <Share></Share>
      </div>
      <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
        <PasswordGenerator @passwordUsed="passwordUsed"></PasswordGenerator>
      </div>
      <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
        <Comment></Comment>
      </div>
      <div class="tab-pane fade" id="contact1" role="tabpanel" aria-labelledby="contact-tab1">
        <Attachments></Attachments>
      </div>
    </div>

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