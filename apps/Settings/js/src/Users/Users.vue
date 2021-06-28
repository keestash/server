<template>
  <div class="d-flex flex-grow-1 flex-column">
    <Loading :loading="!this.loaded"></Loading>
    <UserList
        @usersLoaded="onUsersLoaded"
    ></UserList>
    <AddUser :show="this.loaded"></AddUser>
  </div>
</template>

<script>

import AddUser from "./Users/AddUser";
import UserList from "./Users/UserList";
import Loading from "../../../../../lib/js/src/Components/Loading";

export default {
  name: 'Users',
  components: {
    Loading,
    UserList,
    AddUser
  },
  methods: {
    onUsersLoaded(loaded) {
      this.loaded = loaded;
    },
    countDownChanged(dismissCountDown) {
      this.dismissCountDown = dismissCountDown
    },
  },
  data() {
    return {
      loaded: false,
      updated: false,
      seconds: 0
    }
  }
}
</script>

<style lang="scss">

</style>
