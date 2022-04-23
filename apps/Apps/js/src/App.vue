<template>
  <div class="container">

    <table class="table">
      <thead>
      <tr>
        <th></th>
        <th>{{ $t('table.header.appId') }}</th>
        <th>{{ $t('table.header.version') }}</th>
        <th>{{ $t('table.header.createTs') }}</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="app in apps">
        <td class="collapsing">
          <div class="ui fitted slider checkbox">
            <input
                type="checkbox"
                class="apps__app__checkbox"
                :checked="app.enabled"
                @change="updateApp(app)"
            >
            <label></label>
          </div>
        </td>
        <td>{{ app.id }}</td>
        <td>{{ app.version }}</td>
        <td>{{ formatDate(app.create_ts.date) }}</td>
      </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import {AXIOS, DATE_TIME_SERVICE, StartUp} from "../../../../lib/js/src/StartUp";
import {Container} from "../../../../lib/js/src/DI/Container";
import {ROUTES} from "./config/routes";

export default {
  name: "App",
  data() {
    return {
      apps: []
    }
  },
  methods: {
    formatDate(date) {
      return this.dateTimeService.format(date);
    },
    updateApp(app) {

      this.axios.post(
          ROUTES.getUpdateApps()
          , {
            "activate": !app.enabled
            , "app_id": app.id
          },
      ).then((r) => console.log(r));

    }
  },
  created() {
    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();

    this.container = startUp.getContainer();
    this.axios = this.container.query(AXIOS);
    this.dateTimeService = this.container.query(DATE_TIME_SERVICE);

    this.axios.request(
        ROUTES.getAppsAll()
    )
        .then((r) => {
          return r.data;
        })
        .then((data) => {
          this.apps = data;
        })
    ;
  }
}
</script>

<style scoped>

</style>