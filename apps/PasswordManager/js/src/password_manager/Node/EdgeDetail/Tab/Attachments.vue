<template>
  <div class="tab-pane" id="comment" role="tabpanel">

    <div class="results mt-3 rounded border tab_result_box" id="comment__result">

      <NoDataFound
          :visible="loading === false && (edge.node.attachments || []).length === 0"
          :text="noComments"
          type="attachment"
      ></NoDataFound>

      <template v-if="!this.loading">
        <div class="container">
          <div class="row border-bottom"
               v-if="loading === false && (edge.node.attachments || []).length > 0"
               v-for="attachment in edge.node.attachments || []">

            <div class="col">
              <div class="row justify-content-between">
                <div class="col-sm-6">
                  <div class="row align-items-center">
                    <div class="col-2">
                      <Thumbnail :source="getThumbnailUrl(attachment.jwt)"
                                 :description="attachment.file.name"></Thumbnail>
                    </div>
                    <div class="col">
                      <div class="container">
                        <div class="row cropped">
                          <a :href="getAttachmentUrl(attachment.file.id)"
                             target="_blank">{{
                              attachment.file.name
                            }}</a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-sm-4 align-self-center">
                  <div class="row justify-content-end pr-1">
                    <div class="col-1 mr-2" @click="removeAttachment(attachment)" data-toggle="modal"
                         data-target="#attachment-modal">
                      <i class="fas fa-times remove"></i>
                    </div>
                  </div>
                </div>

              </div>
            </div>

          </div>
        </div>
      </template>
      <Skeleton :count=9 height="25px" v-else/>

    </div>

    <FileUpload
        @upload="upload"
        :message="infoBox"
    ></FileUpload>

    <div>
      <!-- Modal -->
      <div class="modal fade" id="attachment-modal" tabindex="-1" role="dialog" aria-labelledby="attachment-modal"
           aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">
                <template>
                  {{ $t('credential.detail.share.modal.title') }}
                </template>
              </h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="d-block text-center">
                <h3>{{ $t('credential.detail.share.modal.content') }}</h3>
              </div>
              <button type="button" class="btn-block btn-primary mt-3" @click="doRemoveAttachment">
                {{ $t('credential.detail.share.modal.positiveButton') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {APP_STORAGE, AXIOS, StartUp} from "../../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../../../config/routes";
import {RESPONSE_CODE_OK, RESPONSE_FIELD_MESSAGES} from "../../../../../../../../lib/js/src/Backend/Axios";
import {mapState} from "vuex";
import FileUpload from "../../../../../../../../lib/js/src/Components/FileUpload";
import {Skeleton} from 'vue-loading-skeleton';
import Thumbnail from "../../../../../../../../lib/js/src/Components/Thumbnail";
import ContentList from "../../../../../../../../lib/js/src/Components/ContentList";
import _ from "lodash";
import NoDataFound from "../../../../../../../../lib/js/src/Components/NoDataFound";
import Modal from "../../../../../../../../lib/js/src/Components/Modal";

export default {
  name: "Attachments",
  components: {Modal, ContentList, Thumbnail, FileUpload, Skeleton, NoDataFound},
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
      attachmentToDelete: null,
      noComments: "No Attachments",
    }
  },
  methods: {
    removeAttachment(attachment) {
      this.attachmentToDelete = attachment;
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