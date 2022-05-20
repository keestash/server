<template>
  <div class="tab-pane" id="comment" role="tabpanel">
    <ResultBox
        :no-data-found-text="noComments"
        type="comment"
        :can-remove="isOwner"
        :data="edge.node.comments || []"
        @onRemove="removeComment"
        :is-loading="this.loading"
    >
      <template v-slot:title>{{ $t('credential.detail.comment.modal.title') }}</template>
      <template v-slot:body-description></template>
      <template v-slot:body>{{ $t('credential.detail.comment.modal.content') }}</template>
      <template v-slot:button-text>{{ $t('credential.detail.comment.modal.positiveButton') }}</template>
      <template v-slot:negative-button-text>{{ $t('credential.detail.comment.modal.negativeButton') }}</template>
    </ResultBox>

    <form id="pwm__new__comment__form">
      <div class="form-group">
        <label>{{ $t('credential.detail.commentHeader') }}</label>
        <textarea class="form-control" id="pwm__notes__note__area"
                  rows="3" v-model="newComment"></textarea>
      </div>

      <button type="button" class="btn btn-block btn-primary" @click.prevent="addComment">
        {{ $t('credential.detail.addComment') }}
      </button>

    </form>

    <div>

    </div>
  </div>
</template>

<script>
import {APP_STORAGE, AXIOS, DATE_TIME_SERVICE, StartUp} from "../../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../../../../config/routes/index";
import {mapState} from "vuex";
import {Skeleton} from 'vue-loading-skeleton';
import Thumbnail from "../../../../../../../../lib/js/src/Components/Thumbnail";
import NoDataFound from "../../../../../../../../lib/js/src/Components/NoDataFound";
import _ from "lodash";
import ResultBox from "./ResultBox";

export default {
  name: "Comment",
  components: {ResultBox, Thumbnail, Skeleton, NoDataFound},
  computed: {
    isOwner() {
      const userHash = this.appStorage.getUserHash();
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
    this.dateTimeService = this.container.query(DATE_TIME_SERVICE);
    this.appStorage = this.container.query(APP_STORAGE);
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
      newComment: ""
    }
  },
  methods: {
    formatDate(date) {
      return this.dateTimeService.format(date);
    },
    removeComment(comment) {
      this.axios.post(
          ROUTES.getPasswordManagerCommentRemove()
          , {
            commentId: comment.id
          }
      )
          .then((response) => {
            let newNode = _.cloneDeep(this.edge.node);

            for (let i = 0; i < newNode.comments.length; i++) {
              const comment = newNode.comments[i];
              if (parseInt(comment.id) === parseInt(response.data.commentId)) {
                newNode.comments.splice(i, 1);
                break;
              }
            }
            this.$store.dispatch("setSelectedNode", newNode);
          })
          .catch((error) => {
            console.log(error);
          })
    },
    getThumbnail(jwt) {
      return ROUTES.getAssetUrl(jwt);
    },
    getData() {
      if (this.edge === null) return;

      this.axios.request(
          ROUTES.getComments(
              this.edge.node.id
              , 'create_ts'
              , 'desc'
          )
      )
          .then((response) => {
                this.$store.dispatch("updateSelectedEdge", {node: {comments: response.data.comments.content}});
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
            this.$store.dispatch("updateSelectedEdge", {node: {comments: [response.data.comment]}});
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