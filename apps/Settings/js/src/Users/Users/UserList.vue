<template>
  <div class="d-flex">
    <table
        class="table table-borderless table-hover small"
        v-if="this.loading === false"
    >
      <thead>
      <tr>
        <th scope="col" v-for="name in this.fields">{{ name }}</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="(item) in this.items">
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

                if (RESPONSE_CODE_OK in response) {
                  alert("user updated!");
                } else {
                  alert("error!");
                }
              }
          )
          .catch(
              (response) => {
                console.log(response)
              }
          );

    }
  },
  created() {

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
              let content = [];
              if (RESPONSE_CODE_OK in response.data) {
                content = response.data[RESPONSE_CODE_OK].messages.users.content;
              }
              console.log(content)
              this.items = content;
              this.loading = false;
              this.$emit('usersLoaded', true);
            }
        )
        .catch(
            (response) => {
              console.log(response)
            }
        );
  }
  , data() {
    return {
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
