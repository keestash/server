<template>
  <div class="d-flex flex-column flex-grow-1 justify-content-center align-items-center ndf-wrapper"
       v-if="this.visible"
  >
    <i class="fas fa-info fa-5x"></i>
    <h2 class="text-center">{{ this.header }}</h2>
    <p>{{ this.text }}</p>
  </div>
</template>

<script>
export default {
  name: "NoDataFound",
  props: [
    "visible",
    "header",
    "text"
  ]
}
</script>

<style scoped>

</style>