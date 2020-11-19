<template>
  <div class="d-flex justify-content-center my-head-head">
    <h4>{{ this.head.value }}</h4>
  </div>
</template>

<script>
import {ASSET_READER, StartUp} from "../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../lib/js/src/DI/Container";

export default {
  name: "Head",
  props: {
    strings: ''
  },
  data() {
    return {
      head: {
        value: ''
      }
    }
  },
  async created() {
    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();

    this.container = startUp.getContainer();
    const assetReader = this.container.query(ASSET_READER);
    const assets = await assetReader.read(true);
    const strings = JSON.parse(assets[1].install_instance).strings;

    this.head.value = strings.head.value;
  }
}
</script>

<style scoped>

</style>