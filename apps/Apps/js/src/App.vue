<template>
  <div class="container mt-3 mb-3">
    <div class="row">
      <div class="col" v-if="!loading">
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
            <td class="collapsing" v-if="isVisible(app)">
              <div class="ui fitted slider checkbox">
                <input
                    type="checkbox"
                    :checked="app.enabled"
                    @change="updateApp(app)"
                >
                <label></label>
              </div>
            </td>
            <td v-if="isVisible(app)">{{ app.id }}</td>
            <td v-if="isVisible(app)">{{ app.version }}</td>
            <td v-if="isVisible(app)">{{ formatDate(app.create_ts.date) }}</td>
          </tr>
          </tbody>
        </table>

      </div>
      <div class="col text-center" v-else>
        <div class="spinner-grow text-primary spinner-border-sm" role="status"></div>
      </div>
    </div>
  </div>
</template>

<script>
import {AXIOS, DATE_TIME_SERVICE, StartUp} from "../../../../lib/js/src/StartUp";
import {Container} from "../../../../lib/js/src/DI/Container";
import {ROUTES} from "../config/routes/index";
import {EVENT_NAME_GLOBAL_SEARCH} from "../../../../lib/js/src/base";

export default {
  name: "App",
  data() {
    return {
      query: null,
      loading: true,
      apps: []
    }
  },
  methods: {
    isVisible(app) {
      if (this.query === null || this.query === "") return true;
      return (app.id.toLowerCase().includes(this.query.toLowerCase()));
    },
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
      );

    }
  },
  created() {
    const self = this;
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
          this.loading = false;
        })
    ;

    document.addEventListener(
        EVENT_NAME_GLOBAL_SEARCH
        , function (event) {
          self.query = event.detail;
        }
    )
  }
}
</script>

<style scoped>

</style>