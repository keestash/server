<template>
  <div class="container">
    <Header
        title="Organizations"
        sub-title="Manage your Organizations"
    ></Header>
    <AddOrganizations></AddOrganizations>
  </div>
</template>

<script>

import Header from "../../Component/Block/Header";
import Footer from "../../Component/Block/Footer";
import AddOrganizations from "./AddOrganizations/AddOrganizations";
import AddUsers from "./AddUsers/AddUsers";

export default {
  name: "Organization",
  components: {AddUsers, AddOrganizations, Header, Footer}
}
</script>

<style scoped>

</style>