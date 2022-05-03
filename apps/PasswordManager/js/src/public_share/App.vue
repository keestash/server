<template>
  <div>
    <PasswordField @eyeClick="click"></PasswordField>
  </div>
</template>

<script>
import PasswordField from "../Component/PasswordField";
import {AXIOS, StartUp} from "../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../config/routes/index";
import {RESPONSE_CODE_OK, RESPONSE_FIELD_MESSAGES} from "../../../../../lib/js/src/Backend/Axios";

export default {
  name: "App",
  components: {PasswordField},
  data() {
    return {
      hash: null,
      container: {
        container: null,
        services: {
          axios: null
        }
      }
    }
  },
  created() {

    const dataNode = document.getElementById('pwm_ps_data_node');
    this.hash = dataNode.getAttribute('data-hash');
    dataNode.remove();

    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();
    this.container.container = startUp.getContainer();
    this.container.services.axios = this.container.container.query(AXIOS);
  },
  methods: {
    click(data) {

      if (true === data.visible) {
        data.visible = false;
        data.value = null;
        return;
      }

      this.container.services.axios.request(
          ROUTES.getPublicShareDecrypt(
              this.hash
          )
      ).then((response) => {
        if (RESPONSE_CODE_OK in response.data) {
          return response.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES];
        }
        return [];
      }).then((otherData) => {
        data.visible = true;
        data.value = otherData.decrypted;
      })

    }
  }
}
</script>

<style scoped>

</style>