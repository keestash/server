<template>
  <div class="d-flex flex-column flex-grow-1 justify-content-center align-items-center ndf-wrapper share-no-data-found"
       v-if="this.visible"
  >
    <p class="h1 mb-2">
      <i class="blue" :class="this.faClass"></i>
    </p>
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
    "text",
    "type"
  ]
  , computed: {
    faClass: function () {
      if (this.type === 'user') {
        return 'fas fa-user';
      }
      if (this.type === 'attachment') {
        return 'fas fa-file';
      }
      return 'fas fa-info';
    }
  }
}
</script>

<style scoped>

</style>