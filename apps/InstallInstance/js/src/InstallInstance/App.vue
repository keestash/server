<template>
  <div class="d-flex justify-content-center flex-column container">
    <Head></Head>
    <Config></Config>
    <EndUpdate></EndUpdate>
  </div>
</template>

<script>
import Config from "./Config/Config";
import EndUpdate from "./EndUpdate/EndUpdate";
import Head from "./Document/Head/Head";

export default {
  name: "App",
  components: {
    Head,
    EndUpdate,
    Config
  }
}
</script>

<style scoped>

</style>