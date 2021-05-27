<template>
  <div>
    <b-tabs content-class="mt-3" lazy>
      <b-tab
          :title="$t('credential.detail.sharesLabel')"
          active
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

export default {
  name: "Tab",
  components: {Attachments, Comment, PasswordGenerator, Share},
  computed: {
    ...mapState({
      edge: function (state) {
        let edge = state.selectedEdge;
        return edge;
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
  methods: {
    passwordUsed(password) {
      this.$emit("passwordUsed", password);
    }
  }
}
</script>

<style scoped>

</style>