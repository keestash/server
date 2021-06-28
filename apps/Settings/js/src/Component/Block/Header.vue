<template>
  <div class="container mt-3">
    <h3>{{ this.$props.title }}</h3>
    <p>{{ this.$props.subTitle }}</p>
  </div>
</template>

<script>
export default {
  name: "Header",
  props: [
    "title",
    "subTitle"
  ]
}
</script>

<style scoped>

</style>