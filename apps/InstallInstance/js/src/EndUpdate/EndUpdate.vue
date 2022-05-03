<template>
  <div class="container">
    <div class="row">
      <div class="col">
        <button type="button" class="btn btn-primary" @click="buttonClick" :disabled="!enabled || buttonClicked">
          <div v-if="enabled">
            {{ $t('endUpdate.buttonText.enabled') }}
          </div>
          <div v-else>
            {{ $t('endUpdate.buttonText.disabled') }}
          </div>
        </button>
        <div class="spinner-grow text-primary ml-2" role="status" v-if="showSpinner">
          <span class="sr-only"></span>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col">
        <div class="alert alert-success" role="alert" v-if="updatedEnded">
          {{ $t('endUpdate.updateEnded') }}
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import {AXIOS, ROUTER, StartUp} from "../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../config/routes/index";

export default {
  name: "EndUpdate",
  props: {
    enabled: false
  },
  data() {
    return {
      buttonClicked: false,
      updatedEnded: false,
      showSpinner: false
    }
  },
  methods: {
    buttonClick: function () {
      this.buttonClicked = true;
      this.showSpinner = true;
      const startUp = new StartUp(
          new Container()
      );
      startUp.setUp();

      const container = startUp.getContainer();
      const axios = container.query(AXIOS);
      const router = container.query(ROUTER);

      axios.post(
          ROUTES.GET_INSTALL_INSTANCE_END_UPDATE()
      ).then(
          (response) => {
            const data = response.data;
            const routeTo = data.route_to;
            router.route(routeTo);
            this.updatedEnded = true;
            this.showSpinner = false;
            this.buttonClicked = false;
          }
      ).catch((response) => {
        console.error(response);
        this.buttonClicked = false;
        this.showSpinner = false;
      })

    }
  }
}
</script>

<style scoped>

</style>