<template>
  <div class="pwm__row container-fluid" @click="$emit('wasClicked')">
    <div class="row mt-2">
      <div class="col-6 col-md-2 h2 d-flex">
        <b-icon-code-square
            v-if="edge.node.type === 'credential'"
            class="flex-grow-1 flex-shrink-0 node-logo-color"
        ></b-icon-code-square>
        <b-icon-folder-fill
            v-else-if="edge.node.type === 'folder' && isOwner"
            class="flex-grow-1 flex-shrink-0 node-logo-color"
        ></b-icon-folder-fill>
        <b-icon-folder-symlink-fill
            v-else-if="edge.node.type === 'folder' && !isOwner"
            class="flex-grow-1 flex-shrink-0 node-logo-color"
        ></b-icon-folder-symlink-fill>

      </div>
      <div class="col-6 col-md-6">
        <div class="col cropped node-title" :title="edge.node.name">
          <div class="row">
            <div class="col">
              <span class="text-color-grey-dark">{{ edge.node.name }}</span>
              <!--              <br>-->
              <!--              <div v-if="edge.node.type==='credential'">{{ edge.node.username }}</div>-->
              <!--              <div v-else>{{ formatDate(edge.node.create_ts.date) }}</div>-->
            </div>
          </div>
        </div>
      </div>
      <!--      <div class="col-6 col-md-1 contextmenu">-->
      <!--        <i class="fas fa-ellipsis-h fa"></i>-->
      <!--      </div>-->
    </div>

  </div>

</template>

<script>
import {APP_STORAGE, AXIOS, DATE_TIME_SERVICE, StartUp} from "../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../lib/js/src/DI/Container";

export default {
  name: "Edge",
  props: {
    edge: null
  },
  data() {
    return {
      container: {
        container: null,
        services: {
          axios: null,
          appStorage: null,
          dateTimeService: null
        }
      },
    }
  },
  computed: {
    isOwner: function () {
      const userHash = this.container.services.appStorage.getUserHash();
      if (this.edge.node.user.hash === userHash) return true;

      for (let i = 0; i < this.edge.node.shared_to.content.length; i++) {
        const share = this.edge.node.shared_to.content[i];
        if (userHash === share.user.hash) return false;
      }
      return true;
    },
  },
  created: function () {
    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();

    this.container.container = startUp.getContainer();
    this.container.services.axios = this.container.container.query(AXIOS);
    this.container.services.dateTimeService = this.container.container.query(DATE_TIME_SERVICE);
    this.container.services.appStorage = this.container.container.query(APP_STORAGE);
  },
  methods: {
    formatDate: function (date) {
      return this.container.services.dateTimeService.format(date);
    }
  }
}
</script>

<style scoped>

</style>