<template>
  <b-button
      variant="primary"
      @click="buttonClick"
  >End Update
  </b-button>
</template>

<script>
import {RESPONSE_CODE_OK} from "../../../../../../lib/js/src/Backend/Request";
import {AXIOS, ROUTER, StartUp} from "../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../config/routes";

export default {
  name: "EndUpdate",
  methods: {
    buttonClick: () => {

      const startUp = new StartUp(
          new Container()
      );
      startUp.setUp();

      const container = startUp.getContainer();
      const axios = container.query(AXIOS);
      const router = container.query(ROUTER);

      window.setTimeout(
          () => {
            axios.post(
                ROUTES.GET_INSTALL_INSTANCE_END_UPDATE()
                , {}
                , function (x, y, z) {
                  const object = JSON.parse(x);

                  if (RESPONSE_CODE_OK in object) {
                    const routeTo = object[RESPONSE_CODE_OK]['messages']['route_to'];
                    router.route(routeTo);
                  }
                }
                , function (x, y, z) {
                  console.log(x);
                }
            );
          }, 500
      );
    }
  }
}
</script>

<style scoped>

</style>