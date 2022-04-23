<template>
  <div id="no-passwords" v-if="visible"
       class="d-flex flex-column flex-grow-1 justify-content-center align-items-center"
  >
    <div class="d-flex flex-column justify-content-between align-items-center">
      <i class="fas fa-circle-notch fa-3x"></i>
      <p class="uploadmessage">{{ $t('noEdgesInNodeDescription') }}</p>
    </div>
  </div>
</template>

<script>
export default {
  name: "NoEdges",
  props: {
    visible: Boolean
  }
}
</script>

<style scoped>

</style>