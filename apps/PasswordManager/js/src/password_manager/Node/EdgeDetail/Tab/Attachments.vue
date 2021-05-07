<template>
  <div class="tab-pane" id="comment" role="tabpanel">

    <div class="results mt-3 rounded border tab_result_box" id="comment__result">
      <ul class="list-group list-group-flush">
        <div class="spinner-border" role="status" v-if="this.loading"></div>
        <div class="row mx-auto justify-content-center align-items-center flex-column h-100"
             v-if="loading === false && (edge.node.attachments || []).length === 0"
        >
          {{ noAttachments }}
        </div>

        <li class="list-group-item " v-if="loading === false && (edge.node.attachments || []).length > 0"
            v-for="attachment in edge.node.attachments || []">
          <div class="container">
            <div class="row justify-content-between">

              <div class="col-sm">
                <div class="row">
                  <div class="col">
                    {{ attachment.file.name }}
                  </div>
                </div>
              </div>

              <div class="col-sm-4">
                <div class="row justify-content-end pr-1">
                  <div class="col-1">
                    <i class="fas fa-times remove"></i>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </li>

      </ul>
    </div>

    <FileUpload
        @upload="upload"
        :message="infoBox"
    ></FileUpload>
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

export default {
  name: "Attachments",
  components: {FileUpload},
  computed: {
    ...mapState({
      edge: function (state) {
        let edge = state.selectedEdge;
        return edge;
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
    }
  },
  methods: {
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
                console.log(data.fileList.content);
                this.$store.dispatch("setAttachments", data.fileList.content);
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

      console.log(formData)
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
        this.$store.dispatch("addAttachments", data.files);
        this.icons = data.icons;
      })
      console.log(formData)
    },
  }
}
</script>

<style scoped>

</style>