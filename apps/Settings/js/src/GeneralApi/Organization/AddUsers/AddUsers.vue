<template>
  <div>
    <div class="container d-flex flex-grow-1">
      <div class="spinner-grow text-primary align-self-center" role="status" v-if="user.list.length === 0">
        <span class="sr-only"></span>
      </div>

      <div class="row d-flex flex-grow-1 justify-content-start" v-if="user.list.length > 0">
        <ul class="list-group flex-grow-1 flex-basis-0">
          <li class="list-group-item d-flex" @click="openUser(user)"
              v-for="user in user.list"
              v-bind:key="user.id">{{ user.name }}
          </li>
        </ul>
        <div class="flex-grow-1 flex-basis-0">
          <div class="container" v-if="this.content.header !== ''">
            <h4>{{ this.content.header }}</h4>
            <p>Member Organizations: {{ this.content.memberOrganizations.join(", ") }}</p>
            <p>Candidate Organizations: {{ this.content.candidateOrganizations.join(", ") }}</p>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
import {AXIOS, StartUp} from "../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../../config/routes";
import {RESPONSE_CODE_OK, RESPONSE_FIELD_MESSAGES} from "../../../../../../../lib/js/src/Backend/Axios";

export default {
  name: "AddUsers",
  methods: {
    async openUser(user) {
      this.content.header = user.name;
      this.content.memberOrganizations = ["Keestash", "check24", "blalb"];
      this.content.candidateOrganizations = await this.getCandidateOrganizations(this.content.memberOrganizations);
    },
    async getCandidateOrganizations() {
      const allOrganizations = await this.$store.getters.getOrganizations;
      const candidateOrganizations = [];

      for (let i = 0; i < allOrganizations.length; i++) {
        const organization = allOrganizations[i];

        if (!this.content.memberOrganizations.includes(organization.name)) {
          candidateOrganizations.push(organization.name);
        }
      }

      return candidateOrganizations;
    }
  },
  mounted() {
    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();

    this.container = startUp.getContainer();
    const axios = this.container.query(AXIOS);

    axios.request(
        ROUTES.GET_ALL_USERS()
    )
        .then((r) => {
          return r.data;
        })
        .then((data) => {
          if (RESPONSE_CODE_OK in data) {
            return data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES]['users']['content'];
          }
          return [];
        })
        .then(async (users) => {
          this.user.list = users;
        })
  },
  data() {
    return {
      content: {
        header: '',
        memberOrganizations: [],
        candidateOrganizations: []
      },
      user: {
        list: [],
      }
    }
  },
}
</script>

<style scoped>

</style>