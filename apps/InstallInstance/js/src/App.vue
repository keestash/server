<template>
  <div class="container">
    <div class="row">
      <div class="col">
        <Head></Head>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <Config @configFinished="onConfigFinished"></Config>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <EndUpdate :enabled="this.configFinished"></EndUpdate>
      </div>
    </div>
  </div>
</template>

<script>
import Config from "./Config/Config";
import EndUpdate from "./EndUpdate/EndUpdate";
import Head from "./Document/Head/Head";

export default {
  name: "App",
  data() {
    return {
      configFinished: false
    }
  },
  components: {
    Head,
    EndUpdate,
    Config
  },
  methods: {
    onConfigFinished() {
      this.configFinished = true
    }
  }
}
</script>

<style scoped>

</style>