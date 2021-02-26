<template>
  <div class="container d-flex flex-grow-1 justify-content-center flex-column" v-if="this.loading === true">
    <b-spinner class="align-self-center" variant="primary" type="grow" label="Spinning"
    ></b-spinner>
  </div>
</template>

<script>
export default {
  name: "Loading",
  props: ["loading"]
}
</script>

<style scoped>

</style>