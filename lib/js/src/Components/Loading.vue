<template>
  <div class="container d-flex flex-grow-1 justify-content-center flex-column" v-if="this.loading === true">
    <div class="spinner-grow align-self-center text-primary" role="status">
      <span class="sr-only">Loading...</span>
    </div>
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