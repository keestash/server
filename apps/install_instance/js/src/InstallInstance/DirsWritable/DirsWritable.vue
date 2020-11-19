<template>
  <div class="col">
    <h4>{{ this.header.value }}</h4>
    <div class="text-center">
      <b-spinner type="grow" variant="primary" label="Spinning"></b-spinner>
    </div>
  </div>
</template>

<script>
import {ASSET_READER, StartUp} from "../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../lib/js/src/DI/Container";

export default {
  name: "DirsWritable",
  async created() {
    // const startUp = new StartUp(
    //     new Container()
    // );
    // startUp.setUp();
    //
    // const container = startUp.getContainer();
    // const assetReader = container.query(ASSET_READER);
    // const assets = await assetReader.read(true);
    // const strings = JSON.parse(assets[1].dirsWritable).strings;

    // this.form.content.db.host = Object.assign(
    //     this.form.content.db.host,
    //     strings.config.db
    // ).host;
    //
    // this.head.value = strings.config.header;
  },
  data(){
    return{
      header: {
        value: ''
      }
    }
  }
}
</script>

<style scoped>

</style>