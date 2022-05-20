<template>
  <div class="tab-pane" id="comment" role="tabpanel">

    <ResultBox
        :no-data-found-text="noComments"
        type="attachment"
        :can-remove="isOwner"
        :data="edge.node.attachments || []"
        @onRemove="removeAttachment"
        :is-loading="this.loading"
    >
      <template v-slot:title>{{ $t('credential.detail.attachment.modal.title') }}</template>
      <template v-slot:body-description></template>
      <template v-slot:body>{{ $t('credential.detail.attachment.modal.content') }}</template>
      <template v-slot:button-text>{{ $t('credential.detail.attachment.modal.positiveButton') }}</template>
      <template v-slot:negative-button-text>{{ $t('credential.detail.attachment.modal.negativeButton') }}</template>
    </ResultBox>

    <FileUpload
        @upload="upload"
        :message="$t('credential.detail.attachment.fileUpload.message')"
    ></FileUpload>

    <div>

    </div>
  </div>
</template>

<script>
import {APP_STORAGE, AXIOS, StartUp} from "../../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../../../../config/routes/index";
import {RESPONSE_CODE_OK, RESPONSE_FIELD_MESSAGES} from "../../../../../../../../lib/js/src/Backend/Axios";
import {mapState} from "vuex";
import FileUpload from "../../../../../../../../lib/js/src/Components/FileUpload";
import {Skeleton} from 'vue-loading-skeleton';
import Thumbnail from "../../../../../../../../lib/js/src/Components/Thumbnail";
import ContentList from "../../../../../../../../lib/js/src/Components/ContentList";
import _ from "lodash";
import NoDataFound from "../../../../../../../../lib/js/src/Components/NoDataFound";
import ResultBox from "./ResultBox";

export default {
  name: "Attachments",
  components: {ResultBox, ContentList, Thumbnail, FileUpload, Skeleton, NoDataFound},
  computed: {
    isOwner() {
      const userHash = this.storage.getUserHash();
      if (this.edge.node.user.hash === userHash) return true;

      for (let i = 0; i < this.edge.node.shared_to.content.length; i++) {
        const share = this.edge.node.shared_to.content[i];
        if (userHash === share.user.hash) return false;
      }
      return true;
    },
    ...mapState({
      edge: function (state) {
        return state.selectedEdge;
      }
    })
  },
  created() {
    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();

    this.container = startUp.getContainer();
    this.axios = this.container.query(AXIOS);
    this.storage = this.container.query(APP_STORAGE);

    this.getData();
  },
  watch: {
    edge: function () {
      this.getData();
    },
  },
  data() {
    return {
      loading: true,
      noAttachments: "there are no attachments",
      newComment: "",
      noComments: "No Attachments"
    }
  },
  methods: {
    removeAttachment(attachment) {
      this.axios.post(
          ROUTES.getPasswordManagerAttachmentRemove()
          , {
            fileId: attachment.file.id
          }
      )
          .then((response) => {
            if (RESPONSE_CODE_OK in response.data) {
              return response.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES];
            }
            return [];
          })
          .then((data) => {

            let newNode = _.cloneDeep(this.edge.node);

            for (let i = 0; i < newNode.attachments.length; i++) {
              const attachment = newNode.attachments[i];
              if (parseInt(attachment.file.id) === parseInt(data.file.id)) {
                newNode.attachments.splice(i, 1);
                break;
              }
            }

            this.$store.dispatch("setSelectedNode", newNode);
          })
          .catch((error) => {
            console.log(error);
          })
    },
    getAttachmentUrl: function (fileId) {
      return ROUTES.getNodeAttachment(fileId);
    },
    getThumbnailUrl: function (extension) {
      return ROUTES.getAssetUrl(extension);
    },
    getData() {
      if (this.edge === null) return;
      this.axios.request(
          ROUTES.getAttachments(this.edge.node.id)
      )
          .then((response) => {
            if (RESPONSE_CODE_OK in response.data) {
              return response.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES];
            }
            return [];
          })
          .then((data) => {
                this.$store.dispatch("updateSelectedNode", {
                  attachments: data.fileList.content
                });
                this.loading = false;
              }
          )
          .catch((x, y, z) => {
            console.log(x)
          })
    },
    upload(formData) {
      formData.append(
          'node_id', this.edge.node.id
      )

      this.axios.post(
          ROUTES.putAttachments()
          , formData
      ).then((response) => {
        let newNode = _.cloneDeep(this.edge.node);

        for (let i = 0; i < response.data.files.length; i++) {
          if (!Array.isArray(newNode.attachments)) {
            newNode.attachments = [];
          }
          newNode.attachments.push(response.data.files[i]);
        }

        if (response.data.error.length > 0) {
          alert("error with " + response.data.error.length + " files");
        }

        this.$store.dispatch(
            "setSelectedNode"
            , newNode
        );
      })
    },
  }
}
</script>

<style scoped>

</style>