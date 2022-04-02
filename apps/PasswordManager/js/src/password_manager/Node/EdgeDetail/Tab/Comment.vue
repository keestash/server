<template>
  <div class="tab-pane" id="comment" role="tabpanel">

    <div class="results mt-3 rounded border tab_result_box" id="comment__result">

      <NoDataFound
          :visible="loading === false && (edge.node.comments || []).length === 0"
          :text="noComments"
      ></NoDataFound>

      <template v-if="!this.loading">
        <div class="container">
          <div class="row border-bottom"
               v-for="comment in edge.node.comments || []"
               v-if="loading === false && (edge.node.comments || []).length > 0"
          >
            <div class="col">
              <div class="row justify-content-between">
                <div class="col-sm-6">
                  <div class="row align-items-center">
                    <div class="col-2">
                      <Thumbnail
                          :source="getThumbnail(comment.jwt)"
                      ></Thumbnail>
                    </div>
                    <div class="col">
                      <div class="container">
                        <div class="row cropped">
                          {{ comment.comment }}
                        </div>
                        <div class="row">
                          <small>
                            {{ $t('credential.detail.comment.commentedByLabel') }}
                            {{ comment.user.name }} on
                            {{
                              formatDate(comment.create_ts.date)
                            }}</small>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-sm-4 align-self-center">
                  <div class="row justify-content-end pr-1">
                    <div class="col-1 mr-2" @click="removeComment(comment)" data-toggle="modal"
                         data-target="#remove-comment-modal">
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

    <form id="pwm__new__comment__form">
      <div class="form-group">
        <label>{{ $t('credential.detail.commentHeader') }}</label>
        <textarea class="form-control" id="pwm__notes__note__area"
                  rows="3" v-model="newComment"></textarea>
      </div>

      <button type="button" class="btn-block btn-primary" @click.prevent="addComment">
        {{ $t('credential.detail.addComment') }}
      </button>

    </form>

    <div>

      <!-- Modal -->
      <div class="modal fade" id="remove-comment-modal" tabindex="-1" role="dialog"
           aria-labelledby="remove-comment-modal" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">{{ $t('credential.detail.share.modal.title') }}</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="d-block text-center">
                <h3>{{ $t('credential.detail.share.modal.content') }}</h3>
              </div>
              <button type="button" class="btn-block btn-primary mt-3" @click="doRemoveComment">
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
import {AXIOS, DATE_TIME_SERVICE, StartUp} from "../../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../../../config/routes";
import {RESPONSE_CODE_OK, RESPONSE_FIELD_MESSAGES} from "../../../../../../../../lib/js/src/Backend/Axios";
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
    this.dateTimeService = this.container.query(DATE_TIME_SERVICE);

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
    formatDate(date) {
      return this.dateTimeService.format(date);
    },
    removeComment(comment) {
      this.commentToDelete = comment;
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
                this.$store.dispatch("updateSelectedEdge", this.edge);
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
            this.$store.dispatch("updateSelectedEdge", this.edge);
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