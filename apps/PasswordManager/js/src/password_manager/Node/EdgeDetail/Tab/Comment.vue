<template>
  <div class="tab-pane" id="comment" role="tabpanel">

    <div class="results mt-3 rounded border tab_result_box" id="comment__result">
      <ul class="list-group list-group-flush">

        <div class="container-fluid">
          <template v-if="!this.loading">
            <li class="list-group-item border-0" v-if="loading === false && (edge.node.comments || []).length > 0"
                v-for="comment in edge.node.comments || []">
              <div class="container">
                <div class="row justify-content-between">

                  <div class="col-sm">
                    <div class="row">
                      <div class="col d-flex flex-row">
                        <Thumbnail :source="getThumbnail(comment.jwt)"></Thumbnail>
                        {{ comment.comment }}
                      </div>
                    </div>
                  </div>

                  <div class="col-sm-4">
                    <div class="row justify-content-end pr-1">
                      <div class="col-1 mr-2" @click="removeComment(comment)">
                        <i class="fas fa-times remove"></i>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </li>
          </template>
          <Skeleton :count=9 height="25px" v-else/>
        </div>

        <NoDataFound
            :visible="loading === false && (edge.node.comments || []).length === 0"
            :text="noComments"
        ></NoDataFound>

      </ul>
    </div>

    <form id="pwm__new__comment__form">
      <div class="form-group">
        <label for="pwm__notes__note__area">{{ $t('credential.detail.commentHeader') }}</label>
        <textarea class="form-control" id="pwm__notes__note__area"
                  rows="3" v-model="newComment"></textarea>
      </div>

      <b-button
          variant="primary"
          block
          @click.prevent="addComment"
      >{{ $t('credential.detail.addComment') }}
      </b-button>
    </form>

    <div>
      <b-modal ref="comment-modal" hide-footer hide-backdrop no-fade>
        <template #modal-title>
          {{ $t('credential.detail.share.modal.title') }}
        </template>
        <div class="d-block text-center">
          <h3>{{ $t('credential.detail.share.modal.content') }}</h3>
        </div>
        <b-button class="mt-3 btn-primary" block @click="doRemoveComment">
          {{ $t('credential.detail.share.modal.positiveButton') }}
        </b-button>
      </b-modal>
    </div>
  </div>
</template>

<script>
import {RESPONSE_CODE_OK} from "../../../../../../../../lib/js/src/Backend/Request";
import {AXIOS, StartUp} from "../../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../../../config/routes";
import {RESPONSE_FIELD_MESSAGES} from "../../../../../../../../lib/js/src/Backend/Axios";
import {mapState} from "vuex";
import {Skeleton} from 'vue-loading-skeleton';
import Thumbnail from "../../../../../../../../lib/js/src/Components/Thumbnail";
import NoDataFound from "../../../../../../../../lib/js/src/Components/NoDataFound";
import _ from "lodash";

export default {
  name: "Comment",
  components: {Thumbnail, Skeleton, NoDataFound},
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
    removeComment(comment) {
      this.commentToDelete = comment;
      this.$refs['comment-modal'].show();
    },
    doRemoveComment() {
      this.axios.post(
          ROUTES.getPasswordManagerCommentRemove()
          , {
            commentId: this.commentToDelete.id
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

            for (let i = 0; i < newNode.comments.length; i++) {
              const comment = newNode.comments[i];
              if (parseInt(comment.id) === parseInt(data.commentId)) {
                newNode.comments.splice(i, 1);
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
      this.$refs['comment-modal'].hide();
    },
    removeAtWithSlice(array, index) {
      return array.slice(index).concat(array.slice(index + 1));
    },

    getThumbnail(jwt) {
      return ROUTES.getAssetUrl(jwt);
    },
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
          });

    }
  }
}
</script>

<style scoped>

</style>