<template>
  <div class="d-flex justify-content-center my-head-head">
    <h4>{{ this.head.value }}</h4>
  </div>
</template>

<script>

export default {
  name: "Head",
  data() {
    return {
      head: {
        value: ''
      }
    }
  },
  async created() {
    const strings = require(__dirname + '/../../../../../string/frontend/strings.json').strings;
    this.head.value = strings.head.value;
  }
}
</script>

<style scoped>

</style>