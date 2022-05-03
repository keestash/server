<template>
  <div>
    <div class="tab-pane active" id="pwm__sharing" role="tabpanel">
      <div class="mt-3">
        <div class="row">
          <div :class="isOwner ? 'col-11' : 'col-12'">
            <template v-if="state.value === state.states.STATE_LOADED">
              <v-select
                  label="name"
                  :filterable="true"
                  :options="users"
                  @search="onSearch"
                  :placeholder="$t('credential.detail.sharePlaceholder')"
                  @option:selected="onShareSelect"
                  v-if="isOwner"
              >
                <template slot="no-options">
                  {{ $t('credential.detail.sharePlaceholder') }}
                </template>
                <template slot="option" v-slot:option="option">
                  <div class="row">
                    <div class="col-1">
                      <Thumbnail
                          :skip-cache="true"
                          :source="getAssetUrl(option.jwt)"
                      ></Thumbnail>
                    </div>
                    <div class="col-11">
                      {{ option.name }}
                    </div>
                  </div>
                </template>
              </v-select>
            </template>
            <Skeleton height="25px" v-else/>
          </div>

          <template v-if="state.value === state.states.STATE_LOADED">
            <div :class="isOwner ? 'col-1' : 'col-4'" class="d-flex align-middle justify-content-center" v-if="isOwner">
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

        <ResultBox
            :no-data-found-text="noComments"
            :can-remove="isOwner"
            type="user"
            :data="this.edge.node.shared_to.content"
            @onRemove="removeShare"
        ></ResultBox>

      </div>
    </div>
    <div>
      <!-- Modal -->
      <div class="modal fade" id="remove-share-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
           aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">{{ $t('credential.detail.share.modal.title') }}</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                      @click="this.deleteShare.shareModal.hide()">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="d-block text-center">
                <h3>{{ $t('credential.detail.share.modal.content') }}</h3>
              </div>
              <button type="button" class="btn btn-block btn-primary mt-3" @click="doRemoveShare"
                      v-if="this.deleteShare.shareToDelete !== null">
                {{ $t('credential.detail.share.modal.positiveButton') }}
              </button>
              <div class="d-flex justify-content-center" v-else>
                <div class="spinner-grow text-primary" role="status">
                  <span class="sr-only"></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

  </div>
</template>

<script>
import {APP_STORAGE, AXIOS, DATE_TIME_SERVICE, StartUp} from "../../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../../../../config/routes/index";
import {RESPONSE_CODE_OK, RESPONSE_FIELD_MESSAGES} from "../../../../../../../../lib/js/src/Backend/Axios";
import vSelect from 'vue-select'
import {Host} from "../../../../../../../../lib/js/src/Backend/Host";
import {mapState} from "vuex";
import NoDataFound from "../../../../../../../../lib/js/src/Components/NoDataFound";
import Thumbnail from "../../../../../../../../lib/js/src/Components/Thumbnail";
import {Skeleton} from 'vue-loading-skeleton';
import _ from "lodash";
import {Modal} from "bootstrap";
import ResultBox from "./ResultBox";

const STATE_LOADING = 1;
const STATE_LOADED = 2;
export default {
  name: "Share",
  components: {ResultBox, NoDataFound, Thumbnail, Skeleton, vSelect},
  computed: {
    isOwner() {
      const userHash = this.container.services.appStorage.getUserHash();
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
  data() {
    return {
      container: {
        container: null,
        services: {
          axios: null,
          appStorage: null,
          dateTimeService: null
        }
      },
      users: [],
      awaitingSearch: false,
      state: {
        value: STATE_LOADED,
        states: {
          STATE_LOADING: STATE_LOADING,
          STATE_LOADED: STATE_LOADED
        }
      },
      deleteShare: {
        shareToDelete: null,
        shareModal: null
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

    this.container.container = startUp.getContainer();
    this.container.services.axios = this.container.container.query(AXIOS);
    this.container.services.dateTimeService = this.container.container.query(DATE_TIME_SERVICE);
    this.container.services.appStorage = this.container.container.query(APP_STORAGE);

  },
  methods: {
    getPublicShareButtonDescription() {
      return this.edge.node.public_share === null ? this.$t('credential.detail.sharePublicly') : this.$t('credential.detail.copyPublicShareLink');
    },
    getAssetUrl(jsonWebToken) {
      return ROUTES.getAssetUrl(jsonWebToken);
    },
    removeShare(share) {
      const m = new Modal('#remove-share-modal');
      m.show();
      this.deleteShare.shareToDelete = share;
      this.deleteShare.shareModal = m;
    },
    doRemoveShare() {
      this.container.services.axios.post(
          ROUTES.getPasswordManagerShareeRemove()
          , {
            shareId: this.deleteShare.shareToDelete.id
          }
      )
          .then((response) => {
            if (RESPONSE_CODE_OK in response.data) {
              this.deleteShare.shareToDelete = null;
              return response.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES];
            }
            return [];
          })
          .then((data) => {

            let newNode = _.cloneDeep(this.edge.node);

            for (let i = 0; i < newNode.shared_to.content.length; i++) {
              const sharedTo = newNode.shared_to.content[i];
              if (parseInt(sharedTo.id) === parseInt(data.shareId)) {
                newNode.shared_to.content.splice(i, 1);
                --newNode.shared_to.length;
                break;
              }
            }

            this.$store.dispatch("setSelectedNode", newNode);
            this.deleteShare.shareModal.hide();
            this.deleteShare.shareToDelete = null;
          })
          .catch((error) => {
            console.error(error);
            this.deleteShare.shareToDelete = null;
          })
    },
    sharePublicly(e) {
      e.preventDefault();

      const isShared = null !== this.edge.node.public_share && false === this.edge.node.public_share.is_expired;
      if (true === isShared) {
        console.log('still shared, please take the link');
        return true;
      }

      this.container.services.axios.post(
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
      const i = document.createElement("input");
      i.type = "text";
      i.value = text;

      document.body.appendChild(i);

      i.select();
      document.execCommand("Copy");

      document.body.removeChild(i);
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

      this.container.services.axios.post(
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
      this.container.services.axios.request(
          ROUTES.getShareableUsers(
              this.edge.node.id
              , search
          )
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
    },
    formatDate(date) {
      return this.container.services.dateTimeService.format(date);
    }
  }
}
</script>

<style scoped>

</style>