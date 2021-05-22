<template>
  <div>
    <div class="tab-pane active" id="pwm__sharing" role="tabpanel">
      <div class="container mt-3">
        <div class="d-flex">
          <div class="container-fluid p-0">
            <template v-if="state.value === state.states.STATE_LOADED">
              <v-select
                  label="name"
                  :filterable="true"
                  :options="users"
                  @search="onSearch"
                  :placeholder="$t('credential.detail.sharePlaceholder')"
                  @option:selected="onShareSelect"
                  class="flex-grow-1"
              >
                <template slot="no-options">
                  {{ $t('credential.detail.sharePlaceholder') }}
                </template>
                <template slot="option" slot-scope="option">
                  <div class="d-center d-flex flex-row">
                    <Thumbnail
                        :skip-cache="true"
                        :source="getUserProfilePicture(option.jwt)"
                    ></Thumbnail>
                    {{ option.name }}
                  </div>
                </template>
                <template slot="selected-option" slot-scope="option">
                  <div class="selected d-center">
                    {{ option.name }}
                  </div>
                </template>
              </v-select>
            </template>
            <Skeleton height="25px" v-else/>
          </div>
          <template v-if="state.value === state.states.STATE_LOADED">
            <div class="justify-content-between ml-2">
              <button
                  class="btn btn-primary btn-circle btn-sm btn-public-share"
                  @click="sharePublicly"
                  v-if="edge.node.public_share === null"
              >
                <i class="fas fa-share"></i>
              </button>

              <button
                  class="btn btn-primary btn-circle btn-sm"
                  @click="initShareButtonListener"
                  :title=getPublicShareButtonDescription()
                  v-else
              >
                <i class="fas fa-copy"></i>
              </button>

            </div>
          </template>
          <Skeleton height="25px" v-else/>
        </div>

        <div class="results mt-3 rounded border tab_result_box" id="share__results">
          <ul class="list-group list-group-flush">

            <NoDataFound
                :visible="this.edge.node.shared_to.content.length === 0"
                :text="noComments"
                type="user"
                id="share-no-data-found"
            ></NoDataFound>

            <li v-for="share in this.edge.node.shared_to.content" :key="share.id"
                class="list-group-item m-0 pl-0 pr-0 pt-1 pb-1">
              <div class="container">
                <div class="row justify-content-between">
                  <div class="col-sm-4">
                    <div class="row">
                      <div class="col-2">
                        <Thumbnail
                            :source="getUserProfilePicture(share.user.jwt)"
                        ></Thumbnail>
                      </div>
                      <div class="col">
                        <div class="container">
                          <div class="row cropped">
                            {{ share.user.name }}
                          </div>
                          <div class="row">
                            <small> {{ $t('credential.detail.share.sharedDateTimeLabel') }}
                              {{
                                formatDate(share.user.create_ts.date)
                              }}</small>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-sm-4 align-self-center">
                    <div class="row justify-content-end pr-1">
                      <div class="col-1 mr-2" @click="removeShare(share)">
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
    <b-modal ref="my-modal" hide-footer :title="$t('credential.detail.share.modal.title')">
      <div class="d-block text-center">
        <h3>{{ $t('credential.detail.share.modal.content') }}</h3>
      </div>
      <b-button class="mt-3" variant="outline-danger" block @click="hideModal">
        {{ $t('credential.detail.share.modal.negativeButton') }}
      </b-button>
      <b-button class="mt-2" variant="outline-warning" block @click="doRemoveShare()">
        {{ $t('credential.detail.share.modal.positiveButton') }}
      </b-button>
    </b-modal>
  </div>
</template>

<script>
import {AXIOS, DATE_TIME_SERVICE, StartUp} from "../../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../../../config/routes";
import {RESPONSE_CODE_OK, RESPONSE_FIELD_MESSAGES} from "../../../../../../../../lib/js/src/Backend/Axios";
import vSelect from 'vue-select'
import $ from "jquery";
import {Host} from "../../../../../../../../lib/js/src/Backend/Host";
import {mapState} from "vuex";
import NoDataFound from "../../../../../../../../lib/js/src/Components/NoDataFound";
import Thumbnail from "../../../../../../../../lib/js/src/Components/Thumbnail";
import {Skeleton} from 'vue-loading-skeleton';
import _ from "lodash";

const STATE_LOADING = 1;
const STATE_LOADED = 2;
export default {
  name: "Share",
  components: {NoDataFound, vSelect, Thumbnail, Skeleton},
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
      searchQuery: '',
      container: null,
      axios: null,
      users: [],
      awaitingSearch: false,
      state: {
        value: STATE_LOADED,
        states: {
          STATE_LOADING: STATE_LOADING,
          STATE_LOADED: STATE_LOADED
        }
      },
      shareToDelete: null,
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
    this.dateTimeService = this.container.query(DATE_TIME_SERVICE);
  },
  methods: {
    getPublicShareButtonDescription() {
      return this.edge.node.public_share === null ? this.$t('credential.detail.sharePublicly') : this.$t('credential.detail.copyPublicShareLink');
    },
    getUserProfilePicture(jsonWebToken) {
      return ROUTES.getAssetUrl(jsonWebToken);
    },
    removeShare(share) {
      this.shareToDelete = share;
      this.$refs['my-modal'].show();
    },
    doRemoveShare() {
      this.axios.post(
          ROUTES.getPasswordManagerShareeRemove()
          , {
            shareId: this.shareToDelete.id
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

            let newSharedTo = _.cloneDeep(this.edge.shared_to);

            for (let i = 0; i < newSharedTo.content.length; i++) {
              const sharedTo = newSharedTo.content[i];
              if (parseInt(sharedTo.id) === parseInt(data.shareId)) {
                newSharedTo = this.removeAtWithSlice(newSharedTo.content, i);
                break;
              }
            }

            this.$store.dispatch("updateSelectedNode", {
              shared_to: newSharedTo
            });
            this.hideModal();
          })
          .catch((error) => {
            console.log(error);
          })
    },
    hideModal() {
      this.$refs['my-modal'].hide();
    },
    removeAtWithSlice(array, index) {
      return array.slice(index).contact(array.slice(index + 1));
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
                this.$store.dispatch("updateSelectedNode", {
                  public_share: data.share
                });

              }
          )

      ;
    },
    initShareButtonListener(e) {
      e.preventDefault();

      const host = new Host();

      if (null === this.edge.node.public_share) {
        console.log("no public share. Exiting");
        return;
      }

      const url = ROUTES.getPublicShareLink(this.edge.node.public_share.hash);
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
      if (search.length < 3) return;
      if (this.awaitingSearch) return;

      loading(true);
      _.debounce((loading, search, vm) => {
        this.loadUsers(search);
        loading(false);
        this.awaitingSearch = false;
      }, 1000)(loading, search, this)
      this.awaitingSearch = true;
    },
    onShareSelect(option) {
      this.state.value = this.state.states.STATE_LOADING;

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

            const c = [];
            c[this.edge.node.shared_to.length] = data.share;
            const sharedTo = {
              content: c,
              length: this.edge.node.shared_to.length + 1
            };

            this.$store.dispatch("updateSelectedNode", {
              shared_to: sharedTo,
            });

            this.state.value = this.state.states.STATE_LOADED;
          })
          .catch(
              (response) => {
                console.log(response)
              }
          )
    },
    loadUsers(search) {
      this.axios.request(
          ROUTES.getShareableUsers(
              this.edge.node.id
              , search
          )
      )
          .then((response) => {
            console.log(response)
                if (RESPONSE_CODE_OK in response.data) {
                  return response.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES];
                }
                return [];
              }
          ).then((data) => {
            console.log(data)
        this.users = Object.values(
            data.user_list.content
        );
      });
    },
    formatDate(date) {
      return this.dateTimeService.format(date);
    }
  }
}
</script>

<style scoped>

</style>