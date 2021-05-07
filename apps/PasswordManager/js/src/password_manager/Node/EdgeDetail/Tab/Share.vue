<template>
  <div>
    <div class="tab-pane active" id="pwm__sharing" role="tabpanel">
      <div class="container mt-3">
        <div class="medium-6 medium-offset-3 ctrl">

          <v-select label="name" :filterable="false" :options="users" @search="onSearch"
                    :placeholder="$t('credential.detail.sharePlaceholder')"
                    @option:selected="onShareSelect"
          >
            <template slot="no-options">
              {{ $t('credential.detail.sharePlaceholder') }}
            </template>
            <template slot="option" slot-scope="option">
              <div class="d-center">
                {{ option.name }}
              </div>
            </template>
            <template slot="selected-option" slot-scope="option">
              <div class="selected d-center">
                {{ option.name }}
              </div>
            </template>
          </v-select>
        </div>

        <div class="container mt-3">
          <div class="row justify-content-between">
            <div class="col-3 ">
              <button class="btn btn-primary btn-circle btn-sm" id="share_publicly" @click="sharePublicly">
                <i class="fas fa-share"></i>
              </button>
              {{ $t('credential.detail.sharePublicly') }}
            </div>
            <div class="col-2 d-flex flex-column align-items-end" id="copy_link_button"
                 v-if="this.publicShare !== null">
              <button class="btn btn-secondary btn-circle btn-sm" @click="initShareButtonListener"
                      id="ii__copy__link__button"
              >
                <i class="fas fa-copy"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="results mt-3 rounded border tab_result_box" id="share__results">
          <ul class="list-group list-group-flush">

            <NoDataFound
                :visible="this.edge.node.shared_to.content.length === 0"
                :text="noComments"
                type="user"
                id="share-no-data-found"
            ></NoDataFound>

            <li v-for="share in this.edge.node.sharedFormatted" :key="share.id" class="list-group-item shared-user ">
              <div class="container">
                <div class="row justify-content-between">
                  <div class="col-sm-4">
                    <div class="row">
                      <div class="col-2">
                        <img :src="share.user.image" :alt="share.user.name" class="avatar">
                      </div>
                      <div class="col">
                        {{ share.user.name }}
                      </div>
                    </div>
                  </div>

                  <div class="col-sm-4">
                    <div class="row justify-content-end pr-1">
                      <div class="col-1" @click="removeShare(share)">
                        <i class="fas fa-times remove"></i>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </li>

          </ul>
        </div>

      </div>

    </div>
  </div>
</template>

<script>
import {AXIOS, StartUp} from "../../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../../../config/routes";
import {RESPONSE_CODE_OK, RESPONSE_FIELD_MESSAGES} from "../../../../../../../../lib/js/src/Backend/Axios";
import _ from "lodash";
import vSelect from 'vue-select'
import $ from "jquery";
import {Host} from "../../../../../../../../lib/js/src/Backend/Host";
import {mapState} from "vuex";
import NoDataFound from "../../../../../../../../lib/js/src/Components/NoDataFound";

export default {
  name: "Share",
  components: {NoDataFound, vSelect},
  computed: {
    ...mapState({
      edge: function (state) {
        let edge = state.selectedEdge;
        edge.node.sharedFormatted = Object.values(edge.node.shared_to.content);
        return edge;
      }
    })
  },
  data() {
    return {
      searchQuery: '',
      container: null,
      axios: null,
      users: [],
      awaitingSearch: false,
      publicShare: null,
      noComments: "No shared users",
      doWork: () => {
      }
    }
  },
  created() {
    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();

    this.container = startUp.getContainer();
    this.axios = this.container.query(AXIOS);

    this.publicShare = this.edge.node.public_share
  },
  methods: {
    removeShare(share) {
      console.log(share);
      this.axios.post(
          ROUTES.getPasswordManagerShareeRemove()
          , {
            shareId: share.id
          }
      )
          .then((response) => {
            if (RESPONSE_CODE_OK in response.data) {
              console.log("removed share");
              return response.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES];
            }
            return [];
          })
          .then((data) => {
            console.log(data);

            for (let sharedTo in this.edge.shared_to) {
              if (parseInt(sharedTo.id) === parseInt(data.shareId)) {
                // todo remove
                // todo update store
              }
            }
          })
          .catch((error) => {
            console.log(error);
          })
    },
    sharePublicly(e) {
      e.preventDefault();

      const isShared = null !== this.edge.node.public_share && false === this.edge.node.public_share.is_expired;
      if (true === isShared) {
        console.log('still shared, please take the link');
        return true;
      }

      this.axios.post(
          ROUTES.getPasswordManagerSharePublicly()
          , {
            node_id: this.edge.node.id
          }
      )
          .then((response) => {
            if (RESPONSE_CODE_OK in response.data) {
              return response.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES];
            }
            return [];
          })
          .then(
              (data) => {
                this.publicShare = data.share;
              }
          )

      ;
    },
    initShareButtonListener(e) {
      e.preventDefault();

      const host = new Host();

      if (null === this.publicShare) {
        console.log("no public share. Exiting");
        return;
      }

      const url = ROUTES.getPublicShareLink(this.publicShare.hash);
      this.copyToClipboard(url);
      console.log("copied to clipboard");
      console.log(url);
    },
    copyToClipboard(text) {
      const input = $("<input>");
      $("body").append(input);
      input.val(text).select();
      document.execCommand("copy");
      input.remove();
    },
    onSearch(search, loading) {
      if (!search.length) return;
      if (this.awaitingSearch) return;

      loading(true);
      _.debounce((loading, search, vm) => {
        this.loadUsers();
        loading(false);
        this.awaitingSearch = false;
      }, 1000)(loading, search, this)
      this.awaitingSearch = true;
    },
    removeSearchQuery: function () {
      this.searchQuery = '';
    },
    onShareSelect(option) {
      this.axios.post(
          ROUTES.getShare()
          , {
            'node_id': this.edge.node.id
            , "user_id_to_share": option.id
          }
      )
          .then((response) => {
            if (RESPONSE_CODE_OK in response.data) {
              return response.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES];
            }
            return [];
          })
          .then((data) => {
            const e = this.edge;
            e.node.shared_to.content.push(data.share)
            this.$store.dispatch("updateEdge", this.edge);
          })
          .catch(
              (response) => {
                console.log(response)
              }
          )
    },
    loadUsers() {
      this.axios.request(
          ROUTES.getShareableUsers(this.edge.node.id)
      )
          .then((response) => {
                if (RESPONSE_CODE_OK in response.data) {
                  return response.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES];
                }
                return [];
              }
          ).then((data) => {
        this.users = Object.values(
            data.user_list.content
        );
      });
    }
  }
}
</script>

<style scoped>

</style>