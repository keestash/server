<template>
  <div class="flex-grow-1 d-flex justify-content-center">
    <div style="min-width: 70vw">
      <div class="container mt-2">
        <template v-if="this.state.value === this.state.states.STATE_LOADED">
          <h3>{{ this.organization.name }}</h3>
        </template>
        <Skeleton :height="this.state.height" v-else/>
      </div>

      <div class="container">
        <template v-if="this.state.value === this.state.states.STATE_LOADED">
          <p>you can edit the organization here. Add new members, change properties, etc.</p>
        </template>
        <Skeleton :height="this.state.height" v-else/>
      </div>

      <div class="mb-4 pl-5 pr-5">
        <hr class="solid">
      </div>
      <div class="container d-flex mt-2">
        <div class=" row my-1 flex-grow-1 d-flex justify-content-between">
          <div class="col-sm-3">
            <label>ID</label>

            <template v-if="this.state.value === this.state.states.STATE_LOADED">
              <input type="text" class="form-control" v-model="organization.id" readonly>
            </template>
            <Skeleton :height="this.state.height" v-else/>

          </div>
          <div class="col-sm-3">
            <label>Name</label>
            <template v-if="this.state.value === this.state.states.STATE_LOADED">
              <input type="text" class="form-control" id="range-2" v-model="organization.name" @change="onInputChange"
                     readonly>
            </template>
            <Skeleton :height="this.state.height" v-else/>

          </div>
        </div>
      </div>
      <div class="container d-flex">
        <div class="row my-1 flex-grow-1 d-flex justify-content-between">
          <div class="col-sm-3">
            <label>Active</label>
            <template v-if="this.state.value === this.state.states.STATE_LOADED">
              <input type="text"
                     class="form-control"
                     v-model="organization.active_ts === null ? '' : organization.active_ts.date"
                     readonly>
            </template>
            <Skeleton :height="this.state.height" v-else/>

          </div>
          <div class="col-sm-3">
            <label>Created</label>
            <template v-if="this.state.value === this.state.states.STATE_LOADED">
              <input type="text" v-model="organization.create_ts.date" readonly class="form-control">
            </template>
            <Skeleton :height="this.state.height" v-else/>

          </div>
        </div>
      </div>
      <div class="mb-4 pl-5 pr-5">
        <hr class="solid">
      </div>
      <div class="container mt-2">
        <h4>Members</h4>
      </div>
      <div class="container">
        <template v-if="this.state.value === this.state.states.STATE_LOADED">
          <p>you can edit the organization here. Add new members, change properties, etc.</p>
        </template>
        <Skeleton :height="this.state.height" v-else/>
      </div>
      <div class="container mt-2">
        <template v-if="this.state.value === this.state.states.STATE_LOADED">
          <select class="form-control" v-model="candidates.selected" @change="optionSelected">
            <option disabled value="">Please select one</option>
            <option :value="size.id" v-for="size in candidates.values" v-bind:key="candidates.id">
              {{ size.name }}
            </option>
          </select>
        </template>
        <Skeleton :height="this.state.height" v-else/>

      </div>
      <div class="container mt-2">
        <template v-if="this.state.value === this.state.states.STATE_LOADED">
          <ul class="list-group" v-for="user in this.organization.users.content"
              v-if="organization.users.content.length > 0"
              v-bind:key="user.id">
            <li class="list-group-item d-flex justify-content-between">{{ user.name }}
              <span class="badge badge-danger remove-badge align-self-center"
                    @click="handleRemove(user.id)" title="remove">x</span>
            </li>
          </ul>
        </template>
        <Skeleton :height="this.state.height" v-else/>

        <NoDataFound
            :visible="this.organization.users.content.length === 0 && this.state.value === this.state.states.STATE_LOADED"
            header="No Users assigned"
            text="No Users are added to the organization"
        ></NoDataFound>

      </div>
    </div>
  </div>
</template>

<script>
import {AXIOS, StartUp} from "../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../lib/js/src/DI/Container";
import {MODE_ADD, MODE_REMOVE, ROUTES} from "../../../config/routes";
import {RESPONSE_CODE_OK, RESPONSE_FIELD_MESSAGES} from "../../../../../../../lib/js/src/Backend/Axios";
import NoDataFound from "../../../../../../../lib/js/src/Components/NoDataFound";
import {Skeleton} from 'vue-loading-skeleton';

const STATE_LOADING = 1;
const STATE_LOADED = 2;

export default {
  name: "App",
  components: {NoDataFound, Skeleton},
  methods: {

    handleRemove(userId) {
      const startUp = new StartUp(
          new Container()
      );
      startUp.setUp();

      const container = startUp.getContainer();
      const axios = container.query(AXIOS);

      axios.post(
          ROUTES.GET_ORGANIZATION_USER_CHANGE()
          , {
            mode: MODE_REMOVE
            , organization_id: this.organization.id
            , user_id: userId
          }
      );

      let selectedUser = null;
      this.organization.users.content = this.organization.users.content.filter(function (user) {

        if (user.id === userId) {
          selectedUser = user;
        }
        return user.id !== userId;
      });

      this.candidates.values.push(
          selectedUser
      )

      this.$forceUpdate();
    },
    optionSelected(event) {
      const userId = event.target.value;
      const startUp = new StartUp(
          new Container()
      );
      startUp.setUp();

      const container = startUp.getContainer();
      const axios = container.query(AXIOS);

      axios.post(
          ROUTES.GET_ORGANIZATION_USER_CHANGE()
          , {
            mode: MODE_ADD
            , organization_id: this.organization.id
            , user_id: userId
          }
      );

      let selectedUser = null;
      this.candidates.values = this.candidates.values.filter(function (user) {

        if (user.id === userId) {
          selectedUser = user;
        }
        return user.id !== userId;
      });

      this.organization.users.content.push(
          selectedUser
      )

      this.organization.selected = '';
      this.$forceUpdate();
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
        .then((response) => {
          return response.data
        })
        .then((data) => {

          if (RESPONSE_CODE_OK in data) {
            this.organization = data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES].organization;
            this.candidates.values = (data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES].users.content);
            this.state.value = STATE_LOADED;
          }
        })


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

<style scoped>

</style>