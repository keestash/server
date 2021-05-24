<template>
  <div class="tab-pane" id="comment" role="tabpanel">

    <div class="results mt-3 rounded border tab_result_box" id="comment__result">
      <ul class="list-group list-group-flush">
        <div class="container-fluid">
          <template v-if="!this.loading">
            <li class="list-group-item border-0" v-if="loading === false && (edge.node.attachments || []).length > 0"
                v-for="attachment in edge.node.attachments || []">

              <div class="row justify-content-between">

                <div class="col-sm">
                  <div class="row">
                    <div class="col d-flex flex-row">
                      <Thumbnail :source="getThumbnailUrl(attachment.jwt)"
                                 :description="attachment.file.name"></Thumbnail>
                      <b-link :href="getAttachmentUrl(attachment.file.id)" target="_blank">{{
                          attachment.file.name
                        }}
                      </b-link>
                    </div>
                  </div>
                </div>

                <div class="col-sm-4">
                  <div class="row justify-content-end pr-1">
                    <div class="col-1 mr-2" @click="removeAttachment(attachment)">
                      <i class="fas fa-times remove"></i>
                    </div>
                  </div>
                </div>

              </div>
            </li>
          </template>
          <Skeleton :count=9 height="25px" v-else/>
        </div>

        <div class="row mx-auto justify-content-center align-items-center flex-column h-100"
             v-if="loading === false && (edge.node.attachments || []).length === 0"
        >
          {{ noAttachments }}
        </div>


      </ul>
    </div>

    <FileUpload
        @upload="upload"
        :message="infoBox"
    ></FileUpload>

    <div>
      <b-modal ref="attachment-modal" hide-footer hide-backdrop no-fade>
        <template>
          {{ $t('credential.detail.share.modal.title') }}
        </template>
        <div class="d-block text-center">
          <h3>{{ $t('credential.detail.share.modal.content') }}</h3>
        </div>
        <b-button class="mt-3 btn-primary" block @click="doRemoveAttachment">
          {{ $t('credential.detail.share.modal.positiveButton') }}
        </b-button>
      </b-modal>
    </div>

  </div>
</template>

<script>
import {RESPONSE_CODE_OK} from "../../../../../../../../lib/js/src/Backend/Request";
import {APP_STORAGE, AXIOS, StartUp} from "../../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../../../config/routes";
import {RESPONSE_FIELD_MESSAGES} from "../../../../../../../../lib/js/src/Backend/Axios";
import {mapState} from "vuex";
import FileUpload from "../../../../../../../../lib/js/src/Components/FileUpload";
import {Skeleton} from 'vue-loading-skeleton';
import Thumbnail from "../../../../../../../../lib/js/src/Components/Thumbnail";
import ContentList from "../../../../../../../../lib/js/src/Components/ContentList";
import _ from "lodash";

export default {
  name: "Attachments",
  components: {ContentList, Thumbnail, FileUpload, Skeleton},
  computed: {
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
      icons: {},
      noAttachments: "there are no attachments",
      infoBox: "click here to upload or drag",
      newComment: "",
      attachmentToDelete: null
    }
  },
  methods: {
    removeAttachment(attachment) {
      this.attachmentToDelete = attachment;
      this.$refs['attachment-modal'].show();
    },
    doRemoveAttachment() {
      this.axios.post(
          ROUTES.getPasswordManagerAttachmentRemove()
          , {
            fileId: this.attachmentToDelete.file.id
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
            this.hideModal();
          })
          .catch((error) => {
            console.log(error);
          })
    },
    hideModal() {
      this.$refs['attachment-modal'].hide();
    },
    getAttachmentUrl: function (fileId) {
      return ROUTES.getNodeAttachment(fileId);
    },
    getThumbnailUrl: function (extension) {
      return ROUTES.getAssetUrl(extension);
    },
    getData() {
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
      ).then(
          (response => {
            if (RESPONSE_CODE_OK in response.data) {
              return response.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES];
            }
            return [];
          })
      ).then((data) => {

        let newNode = _.cloneDeep(this.edge.node);

        for (let i = 0; i < data.files.length; i++) {
          if (!Array.isArray(newNode.attachments)) {
            newNode.attachments = [];
          }
          newNode.attachments.push(data.files[i]);
        }

        this.$store.dispatch(
            "setSelectedNode"
            , newNode
        );
        this.icons = data.icons;
      })
    },
  }
}
</script>

<style scoped>

</style>