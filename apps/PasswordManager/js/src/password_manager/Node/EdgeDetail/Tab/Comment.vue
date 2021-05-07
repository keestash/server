<template>
  <div class="tab-pane" id="comment" role="tabpanel">

    <div class="results mt-3 rounded border tab_result_box" id="comment__result">
      <ul class="list-group list-group-flush">
        <div class="spinner-border" role="status" v-if="this.loading"></div>
        <div class="row mx-auto justify-content-center align-items-center flex-column h-100"
             v-if="loading === false && (edge.node.comments || []).length === 0"
        >
          {{ noComments }}
        </div>

        <li class="list-group-item " v-if="loading === false && (edge.node.comments || []).length > 0"
            v-for="comment in edge.node.comments || []">
          <div class="container">
            <div class="row justify-content-between">

              <div class="col-sm">
                <div class="row">
                  <div class="col">
                    {{ comment.comment }}
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

    <form id="pwm__new__comment__form">
      <div class="form-group">
        <label for="pwm__notes__note__area">{{ $t('credential.detail.commentHeader') }}</label>
        <textarea class="form-control" id="pwm__notes__note__area"
                  rows="3" v-model="newComment"></textarea>
      </div>

      <button
          class="btn btn-primary"
          @click.prevent="addComment"
      >{{ $t('credential.detail.addComment') }}
      </button>
    </form>
  </div>
</template>

<script>
import {RESPONSE_CODE_OK} from "../../../../../../../../lib/js/src/Backend/Request";
import {AXIOS, StartUp} from "../../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../../../config/routes";
import {RESPONSE_FIELD_MESSAGES} from "../../../../../../../../lib/js/src/Backend/Axios";
import {mapState} from "vuex";

export default {
  name: "Comment",
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

    this.getData();
  },
  watch: {
    edge: function () {
      this.getData();
    }
  },
  data() {
    return {
      loading: true,
      noComments: "No Comments there",
      newComment: "",
    }
  },
  methods: {
    getData() {
      this.axios.request(
          ROUTES.getComments(this.edge.node.id)
      )
          .then((response) => {
            if (RESPONSE_CODE_OK in response.data) {
              return response.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES];
            }
            return [];
          })
          .then((data) => {
                this.edge.node.comments = data.comments.content;
                this.$store.dispatch("updateEdge", this.edge);
                this.loading = false;
              }
          )
          .catch((x, y, z) => {
            console.log(x)
          })
    },
    addComment() {

      this.axios.post(
          ROUTES.getAddComment()
          , {
            node_id: this.edge.node.id
            , comment: this.newComment
          }
      )
          .then((response) => {
            if (RESPONSE_CODE_OK in response.data) {
              return response.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES];
            }
            return [];
          })
          .then((data) => {

            this.edge.node.comments.push(data.comment);
            this.$store.dispatch("updateEdge", this.edge);
            this.newComment = "";

          })
          .catch((e) => {
            console.log(e)
          })
      ;


    }
  }
}
</script>

<style scoped>

</style>