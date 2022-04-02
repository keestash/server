<template>
  <div class="container d-flex flex-grow-1 justify-content-center flex-column">
    <Loading :loading="this.state.value === this.state.VALUES.STATE_LOADING"></Loading>
    <NoDataFound
        :visible="this.state.value === this.state.VALUES.STATE_LOADED_NO_ORGANIZATIONS"
        header="No Organizations Yet"
        text="There are no Organizations Yet"
    ></NoDataFound>

    <div class="row mb-4" id="organization-list"
         v-if="this.state.value === this.state.VALUES.STATE_LOADED_ORGANIZATIONS_EXIST">
      <ul class="list-group flex-grow-1">

        <li class="list-group-item d-flex justify-content-between align-items-center"
            v-for="organization in organization.list"
            v-bind:key="organization.id"
        >
          <a :href="getOrganizationDetailLink(organization)" target="_blank" class="link-primary">{{
              organization.name
            }}</a>
          <div>
            <span v-if="organization.active_ts !== null" class="activation-badge badge badge-danger"
                  @click="handleActivation(false, organization.id)" :title="formatDate(organization.active_ts)">deactivate</span>
            <span class="activation-badge badge badge-success" @click="handleActivation(true, organization.id)"
                  v-if="organization.active_ts === null">activate</span>

            <span class="badge badge-pill badge-primary">{{ organization.users.length }}</span>
          </div>
        </li>
      </ul>
    </div>
    <div class="row">
      <form class="d-inline flex-grow-1" @submit="this.submit">
        <div class="form-group">
          <label for="new-organization">Organization Name</label>
          <input type="text" class="form-control flex-grow-1" id="new-organization"
                 :placeholder="this.messages.organization.new.placeholder" v-model="organization.new.text"
          >
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import {AXIOS, StartUp} from "../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../../config/routes";
import {RESPONSE_CODE_OK, RESPONSE_FIELD_MESSAGES} from "../../../../../../../lib/js/src/Backend/Axios";
import moment from "moment";
import NoDataFound from "../../../../../../../lib/js/src/Components/NoDataFound";
import Loading from "../../../../../../../lib/js/src/Components/Loading";

const STATE_LOADING = 1;
const STATE_LOADED_NO_ORGANIZATIONS = 2;
const STATE_LOADED_ORGANIZATIONS_EXIST = 3;

export default {
  name: "AddOrganizations",
  components: {Loading, NoDataFound},
  methods: {
    getOrganizationDetailLink(organization) {
      return ROUTES.GET_ORGANIZATION_DETAILS(organization);
    },
    formatDate(date) {
      return moment(date.date).format('DD.MM.YYYY hh:mm:ss');
    },
    handleActivation(activate, organizationId) {
      console.log(activate)
      const axios = this.container.query(AXIOS);
      axios.post(
          ROUTES.GET_ORGANIZATION_ACTIVATE()
          , {
            id: organizationId
            , activate: !!activate
          }
      )
          .then((response) => {
            return response.data;
          })
          .then((data) => {

            console.log(data);
            if (RESPONSE_CODE_OK in data) {
              const organization = data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES]['organization'];

              for (let i = 0; i < this.organization.list.length; i++) {
                const orga = this.organization.list[i];

                if (orga.id === organization.id) {
                  this.organization.list[i] = organization;
                  this.$forceUpdate();
                  break;
                }
              }

            }
          })
    },
    submit(event) {
      event.preventDefault();
      const axios = this.container.query(AXIOS);
      axios.post(
          ROUTES.GET_ORGANIZATION_ADD()
          , {
            organization: this.organization.new.text
          }
      )
          .then(
              (r) => {
                return r.data;
              }
          )
          .then(
              (data) => {

                if (RESPONSE_CODE_OK in data) {
                  const organization = data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES]['organization'];
                  this.organization.new.text = '';
                  this.organization.list.push(organization);
                  this.state.value = STATE_LOADED_ORGANIZATIONS_EXIST;
                  return;
                }
                console.log("could not add");
              }
          )

    }

  },
  data() {
    return {
      state: {
        value: STATE_LOADING,
        VALUES: {
          STATE_LOADING: STATE_LOADING,
          STATE_LOADED_NO_ORGANIZATIONS: STATE_LOADED_NO_ORGANIZATIONS,
          STATE_LOADED_ORGANIZATIONS_EXIST: STATE_LOADED_ORGANIZATIONS_EXIST
        }
      },
      messages: {
        organization: {
          new: {
            placeholder: "please input a new organization"
          }
        }
      },
      organization: {
        list: [],
        new: {
          text: ""
        }
      }
    }
  },
  created() {
    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();

    this.container = startUp.getContainer();
    const axios = this.container.query(AXIOS);

    axios.request(
        ROUTES.GET_ORGANIZATION_LIST(true)
    )
        .then(
            (r) => {
              return r.data
            }
        )
        .then(
            (data) => {
              return data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES]["organizations"];
            }
        )
        .then(
            (organizations) => {
              console.log(organizations.length);
              this.organization.list = organizations;
              this.state.value = this.organization.list.length === 0 ? STATE_LOADED_NO_ORGANIZATIONS : STATE_LOADED_ORGANIZATIONS_EXIST;
            }
        )

  }
}
</script>

<style scoped>

</style>