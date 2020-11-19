<template>
  <div class="d-flex justify-content-center flex-column container">
    <Head></Head>
    <Config></Config>
    <DirsWritable></DirsWritable>
    <HasDataDirs></HasDataDirs>
    <EndUpdate></EndUpdate>
  </div>
</template>

<script>
import Config from "./Config/Config";
import DirsWritable from "./DirsWritable/DirsWritable";
import EndUpdate from "./EndUpdate/EndUpdate";
import HasDataDirs from "./HasDataDirs/HasDataDirs";
import Head from "./Document/Head/Head";

export default {
  name: "App",
  components: {Head, HasDataDirs, EndUpdate, DirsWritable, Config},

}
</script>

<style scoped>

</style>