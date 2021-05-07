<template>
  <div class="d-flex flex-column flex-grow-1 justify-content-center align-items-center ndf-wrapper"
       v-if="this.visible"
  >
    <i :class="this.faClass"></i>
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
        return 'fas fa-user fa-5x';
      }
      return 'fas fa-info fa-5x';
    }
  }
}
</script>

<style scoped>

</style>