<template>
  <div class="col">
    <h4>{{ this.$props.header }}</h4>
    <div class="text-center">
      <b-spinner type="grow" variant="primary" label="Spinning"></b-spinner>
    </div>
  </div>
</template>

<script>
export default {
  name: "HasDataDirs",
  props: {
    header: ''
  }
}
</script>

<style scoped>

</style>