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
      console.log("starting to end update ....");
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
            ).then(
                (response) => {
                  console.log("done");
                  const object = response.data;
console.log(response);
                  console.log(object)
                  if (RESPONSE_CODE_OK in object) {
                    console.log("success. Routing ....");
                    const routeTo = object[RESPONSE_CODE_OK]['messages']['route_to'];
                    router.route(routeTo);
                    return;
                  }
                  console.log("Error :(");
                }
            ).catch((response) => {
              console.log(response);
            })
            ;
          }, 500
      );
    }
  }
}
</script>

<style scoped>

</style>