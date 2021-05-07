<template>
  <div id="no-passwords" v-if="visible"
       class="d-flex flex-column flex-grow-1 justify-content-center align-items-center"
  >
    <div class="d-flex flex-column justify-content-between align-items-center">
      <i class="fas fa-key fa-3x"></i>
      <h3 class="text-color-grey-dark">{{ $t('nothingClicked') }}</h3>
      <p class="uploadmessage">{{ $t('nothingClickedDescription') }}</p>
    </div>

  </div>
</template>

<script>
export default {
  name: "NoNodeSelected",
  props: {
    visible: true
  }
}
</script>

<style scoped>

</style>