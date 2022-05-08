<template>
  <div class="flex-grow-1 d-flex justify-content-center">
    <div style="min-width: 70vw">
      <div class="container mt-3">
        <template v-if="this.state.value === this.state.states.STATE_LOADED">
          <h3>{{ this.organization.name }}</h3>
        </template>
        <IsLoading v-else class="organization-is-loading"></IsLoading>
      </div>

      <div class="container">
        <p>{{ $t('organization.description') }}</p>
      </div>

      <div class="mb-4 ps-5 pe-5">
        <hr class="solid">
      </div>
      <div class="container d-flex mt-2">
        <div class=" row my-1 flex-grow-1 d-flex justify-content-between">
          <div class="col-sm-3">
            <label>{{ $t('organization.id.label') }}</label>

            <template v-if="this.state.value === this.state.states.STATE_LOADED">
              <input type="text" class="form-control" v-model="organization.id" readonly>
            </template>
            <IsLoading v-else class="organization-is-loading"></IsLoading>

          </div>
          <div class="col-sm-3">
            <label>{{ $t('organization.name.label') }}</label>
            <template v-if="this.state.value === this.state.states.STATE_LOADED">
              <input type="text" class="form-control" id="range-2" v-model="organization.name" @change="onInputChange"
                     readonly>
            </template>
            <IsLoading v-else class="organization-is-loading"></IsLoading>

          </div>
        </div>
      </div>
      <div class="container d-flex">
        <div class="row my-1 flex-grow-1 d-flex justify-content-between">
          <div class="col-sm-3">
            <label>{{ $t('organization.active.label') }}</label>
            <template v-if="this.state.value === this.state.states.STATE_LOADED">
              <input type="text"
                     class="form-control"
                     v-model="organization.active_ts.date"
                     readonly>
            </template>
            <IsLoading v-else class="organization-is-loading"></IsLoading>

          </div>
          <div class="col-sm-3">
            <label>{{ $t('organization.created.label') }}</label>
            <template v-if="this.state.value === this.state.states.STATE_LOADED">
              <input type="text" v-model="organization.create_ts.date" readonly class="form-control">
            </template>
            <IsLoading v-else class="organization-is-loading"></IsLoading>

          </div>
        </div>
      </div>
      <div class="mb-4 ps-5 pe-5">
        <hr class="solid">
      </div>
      <div class="container mt-2">
        <h4>Members</h4>
      </div>
      <div class="container">
        <p>{{ $t('organization.members.description') }}</p>
      </div>
      <div class="container mt-2">
        <template v-if="this.state.value === this.state.states.STATE_LOADED">
          <select class="form-control" v-model="candidates.selected" @change="optionSelected">
            <option disabled :value="$t('organization.members.dropdown.firstElement')"></option>
            <option :value="size.id" v-for="size in candidates.values" v-bind:key="candidates.id">
              {{ size.name }}
            </option>
          </select>
        </template>
        <IsLoading v-else class="organization-is-loading"></IsLoading>

      </div>
      <div class="container mt-2">
        <template v-if="this.state.value === this.state.states.STATE_LOADED">
          <ul class="list-group" v-for="user in this.organization.users.content"
              v-if="organization.users.content.length > 0"
              v-bind:key="user.id">
            <li class="list-group-item d-flex justify-content-between">{{ user.name }}
              <span class="badge bg-danger remove-badge align-self-center"
                    @click="handleRemove(user.id)" title="remove">x</span>
            </li>
          </ul>
        </template>
        <IsLoading v-else class="organization-is-loading"></IsLoading>

        <NoDataFound
            :visible="this.organization.users.content.length === 0 && this.state.value === this.state.states.STATE_LOADED"
            :header="$t('organization.members.noUser')"
            :text="$t('organization.members.noUserDescription')"
        ></NoDataFound>

      </div>
    </div>
  </div>
</template>

<script>
import {AXIOS, StartUp} from "../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../lib/js/src/DI/Container";
import {MODE_ADD, MODE_REMOVE, ROUTES} from "../../../../config/routes/index";
import NoDataFound from "../../../../../../../lib/js/src/Components/NoDataFound";
import {Skeleton} from 'vue-loading-skeleton';
import IsLoading from "../../../../../../../lib/js/src/Components/IsLoading";

const STATE_LOADING = 1;
const STATE_LOADED = 2;

export default {
  name: "App",
  components: {IsLoading, NoDataFound, Skeleton},
  methods: {

    handleRemove: function (userId) {
      userId = parseInt(userId);
      const startUp = new StartUp(
          new Container()
      );
      startUp.setUp();
      const self = this;
      const container = startUp.getContainer();
      const axios = container.query(AXIOS);

      axios.post(
          ROUTES.GET_ORGANIZATION_USER_CHANGE()
          , {
            mode: MODE_REMOVE
            , organization_id: this.organization.id
            , user_id: userId
          }
      )
          .then(
              function () {
                let selectedUser = null;
                self.organization.users.content =
                    self.organization.users.content.filter(
                        function (user) {

                          if (user.id === userId) {
                            selectedUser = user;
                          }
                          return user.id !== userId;
                        }
                    );

                self.candidates.values.push(
                    selectedUser
                )

                // self.$forceUpdate();
              }
          )
    },
    optionSelected: function (event) {
      const userId = parseInt(event.target.value);
      const startUp = new StartUp(
          new Container()
      );
      startUp.setUp();

      const self = this;
      const container = startUp.getContainer();
      const axios = container.query(AXIOS);

      axios.post(
          ROUTES.GET_ORGANIZATION_USER_CHANGE()
          , {
            mode: MODE_ADD
            , organization_id: this.organization.id
            , user_id: userId
          }
      )
          .then(
              function () {
                let selectedUser = null;
                self.candidates.values =
                    self.candidates.values.filter(function (user) {

                      if (user.id === userId) {
                        selectedUser = user;
                      }
                      return user.id !== userId;
                    });

                self.organization.users.content.push(
                    selectedUser
                )

                self.organization.selected = '';
                // self.$forceUpdate();
              }
          )

    },
    onInputChange() {
      const startUp = new StartUp(
          new Container()
      );
      startUp.setUp();

      const container = startUp.getContainer();
      const axios = container.query(AXIOS);

      axios.post(
          ROUTES.GET_ORGANIZATION_UPDATE()
          , {
            organization: JSON.stringify(this.organization)
          }
      )
          .then((r) => {
            return r.data;
          })
          .then((data) => {
            console.log(data);
          })
    }
  },

  mounted() {
    const dataNode = document.getElementById("organization-detail-data-node");
    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();

    const container = startUp.getContainer();
    const axios = container.query(AXIOS);

    axios.request(
        ROUTES.GET_ORGANIZATION_GET(dataNode.dataset.organization_id)
    )
        .then(
            (response) => {
              const data = response.data;
              this.organization = data.organization;
              this.candidates.values = data.users.content;
              this.state.value = STATE_LOADED;
            }
        )

  },
  data() {
    return {
      state: {
        height: "25px",
        value: STATE_LOADING,
        states: {
          STATE_LOADING: STATE_LOADING,
          STATE_LOADED: STATE_LOADED
        }
      },
      organization: {
        name: '',
        id: '',
        active_ts: {
          date: ''
        },
        create_ts: {
          date: ''
        },
        users: {
          content: []
        }
      },
      candidates: {
        selected: '',
        values: []
      },
    }
  }
}
</script>

<style scoped lang="scss">
.organization-is-loading {
  height: 30px;
}
</style>