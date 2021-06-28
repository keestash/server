<template>
  <div class="d-flex">
    <b-table
        borderless
        small
        hover
        :items="items"
        :fields="fields"
        v-if="this.loading === false"
    >
      <template v-slot:cell(name)="row">
        <b-form-input
            v-model="row.item.name"
            debounce="500"
            @change="onInputChange(row.item)"
            class="user-input"
        />
      </template>

      <template v-slot:cell(first_name)="row">
        <b-form-input
            v-model="row.item.first_name"
            debounce="500"
            @change="onInputChange(row.item)"
            class="user-input"
        />
      </template>

      <template v-slot:cell(last_name)="row">
        <b-form-input
            v-model="row.item.last_name"
            debounce="500"
            @change="onInputChange(row.item)"
            class="user-input"
        />
      </template>

      <template v-slot:cell(email)="row">
        <b-form-input
            v-model="row.item.email"
            debounce="500"
            @change="onInputChange(row.item)"
            class="user-input"
        />
      </template>
    </b-table>
  </div>
</template>

<script>
import {ROUTES} from "../../config/routes";
import {AXIOS} from "../../../../../../lib/js/src/StartUp";
import {RESPONSE_CODE_OK} from "../../../../../../lib/js/src/Backend/Axios";
import Loading from "../../../../../../lib/js/src/Components/Loading";

const diContainer = Keestash.Main.getContainer();
export default {
  name: "UserList"
  ,
  components: {Loading},
  methods: {
    onInputChange(item) {

      const axios = diContainer.query(AXIOS);

      axios.post(
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
    const axios = diContainer.query(AXIOS);

    axios.request(
        ROUTES.USERS_ALL()
    )
        .then(
            (response) => {
              let content = [];
              if (RESPONSE_CODE_OK in response.data) {
                content = response.data[RESPONSE_CODE_OK].messages.users.content;
              }
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
