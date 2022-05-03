<template>
  <div class="d-flex">
    <table
        class="table"
        v-if="this.loading === false"
    >
      <thead>
      <tr>
        <th scope="col">{{ $t('table.head.name') }}</th>
        <th scope="col">{{ $t('table.head.firstName') }}</th>
        <th scope="col">{{ $t('table.head.lastName') }}</th>
        <th scope="col">{{ $t('table.head.email') }}</th>
        <th scope="col">{{ $t('table.head.phone') }}</th>
        <th scope="col">{{ $t('table.head.website') }}</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="(item) in this.items" v-show="onSearch(query,item)">
        <th scope="row">
          <div class="form-group">
            <input
                type="text"
                class="form-control"
                v-model="item.name"
                @change="onInputChange(item)"
            >
          </div>
        </th>
        <th scope="row">
          <div class="form-group">
            <input
                type="text"
                class="form-control"
                v-model="item.first_name"
                @change="onInputChange(item)"
            >
          </div>
        </th>
        <th scope="row">
          <div class="form-group">
            <input
                type="text"
                class="form-control"
                v-model="item.last_name"
                @change="onInputChange(item)"
            >
          </div>
        </th>
        <th scope="row">
          <div class="form-group">
            <input
                type="text"
                class="form-control"
                v-model="item.email"
                @change="onInputChange(item)"
            >
          </div>
        </th>
        <th scope="row">
          <div class="form-group">
            <input
                type="text"
                class="form-control"
                v-model="item.phone"
                @change="onInputChange(item)"
            >
          </div>
        </th>
        <th scope="row">
          <div class="form-group">
            <input
                type="text"
                class="form-control"
                v-model="item.website"
                @change="onInputChange(item)"
            >
          </div>
        </th>
      </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import {ROUTES} from "../../config/routes";
import {AXIOS, StartUp} from "../../../../../../lib/js/src/StartUp";
import {RESPONSE_CODE_OK} from "../../../../../../lib/js/src/Backend/Axios";
import Loading from "../../../../../../lib/js/src/Components/Loading";
import {Container} from "../../../../../../lib/js/src/DI/Container";
import {EVENT_NAME_GLOBAL_SEARCH} from "../../../../../../lib/js/src/base";

export default {
  name: "UserList",
  components: {Loading},
  methods: {
    onInputChange(item) {
      this.axios.post(
          ROUTES.USERS_EDIT()
          , {
            user: JSON.stringify(item)
          }
      )
          .then((r) => {
            return r.data;
          })
          .then(
              (response) => {
                alert("user updated!");
              }
          )
          .catch(
              (response) => {
                console.error(response)
              }
          );
    },
    onSearch(val, user) {
      if (user === null || typeof user === 'undefined') return true;
      if (val === "" || val === null) return true;
      return user.name.toLowerCase().includes(val.toLowerCase())
          || user.first_name.toLowerCase().includes(val.toLowerCase())
          || user.last_name.toLowerCase().includes(val.toLowerCase())
          || user.email.toLowerCase().includes(val.toLowerCase())
          || user.phone.toLowerCase().includes(val.toLowerCase())
          || user.website.toLowerCase().includes(val.toLowerCase());
    },
  },
  created() {
    const self = this;
    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();

    this.container = startUp.getContainer();
    this.axios = this.container.query(AXIOS);

    this.axios.request(
        ROUTES.USERS_ALL()
    )
        .then(
            (response) => {
              this.items = response.data.users.content;
              this.loading = false;
              this.$emit('usersLoaded', true);
            }
        )
        .catch(
            (response) => {
              console.error(response)
            }
        );

    document.addEventListener(
        EVENT_NAME_GLOBAL_SEARCH
        , function (event) {
          self.query = event.detail;
        }
    )
  }
  , data() {
    return {
      query: null,
      loading: true,
      updated: null,
      container: null,
      axios: null,
      fields: [
        'name'
        , 'first_name'
        , 'last_name'
        , 'email'
      ],
      items: []
    }
  },
}
</script>

<style scoped lang="scss"></style>
