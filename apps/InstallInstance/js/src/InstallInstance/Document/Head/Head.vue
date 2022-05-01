<template>
  <div class="container-fluid mt-3 mb-3">
    <div class="row text-center">
      <div class="col">
        <h4>{{ $t('head.value') }}</h4>
      </div>
    </div>
    <div class="row">
      <div class="col">
        {{$t('head.detail')}}
      </div>
    </div>
  </div>
</template>

<script>

export default {
  name: "Head"
}
</script>

<style scoped lang="scss">
</style>