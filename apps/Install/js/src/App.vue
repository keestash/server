<template>
  <div>
    <div class="container mt-3 mb-3">

      <div class="row">
        <div class="col">
          <h1>{{ $t('installationHeader') }}</h1>
        </div>
      </div>


      <div class="row">
        <div class="col">

          <div class="text-center" v-if="values.installType === -1">
            <div class="spinner-grow text-primary" role="status">
              <span class="sr-only"></span>
            </div>
          </div>

          <p class="lead" v-else-if="values.installType === 0">{{ $t('installInstructionNothingToUpdate') }}</p>
          <p class="lead" v-else-if="values.installType === 1">{{ $t('installInstructionInstallApps') }} </p>
          <p class="lead" v-else-if="values.installType === 2">{{ $t('installInstructionUpdateApps') }}</p>
          <p class="lead" v-else-if="values.installType === 3">{{ $t('installInstructionInstallUpdateApps') }}</p>
          <p class="lead" v-else-if="values.installType === 4">{{ $t('installationDescriptionError') }}</p>
        </div>
      </div>

      <div class="row" v-for="app in values.appsToInstall">
        <div class="col">
          <div class="card mb-3"
               v-bind:key="app.id">
            <div class="card-body">
              <h5 class="card-title">{{ app.name }}</h5>
              <div class="container">
                <div class="row">
                  <div class="col">
                    {{ $t('id') }}: {{ app.id }}
                  </div>
                </div>

                <div class="row">
                  <div class="col">
                    {{ $t('order') }}: {{ app.order }}
                  </div>
                </div>

                <div class="row">
                  <div class="col">
                    {{ $t('baseRoute') }}: {{ app.base_route }}
                  </div>
                </div>

                <div class="row">
                  <div class="col">
                    {{ $t('version') }}: {{ app.version }}
                  </div>
                </div>
              </div>
            </div>
            <div class="card-footer bg-transparent border-success">{{ $t('appInstallDescription') }}</div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col">

          <button class="btn btn-primary" @click="handleClick" v-if="values.installType !== -1">
            <span v-if="values.installType === 0">
              {{ $t('endUpdate') }}
            </span>
            <span v-else-if="values.installType === 1">
              {{ $t('installApps') }}
            </span>
            <span v-else-if="values.installType === 2">
              {{ $t('updateApps') }}
            </span>
            <span v-else-if="values.installType === 3">
              {{ $t('updateApps') }}
            </span>
          </button>

        </div>
      </div>

    </div>

  </div>
</template>

<script>
import {AXIOS, ROUTER, StartUp} from "../../../../lib/js/src/StartUp";
import {Container} from "../../../../lib/js/src/DI/Container";
import {ROUTES} from "../config/routes/index";

const INSTALL_TYPE_CONFIG_NOT_LOADED = -1;
const INSTALL_TYPE_CONFIG_NOTHING_TO_BE_UPDATED = 0;
const INSTALL_TYPE_CONFIG_APPS_NEED_TO_BE_INSTALLED = 1;
const INSTALL_TYPE_CONFIG_APPS_NEED_TO_BE_UPDATED = 2;
const INSTALL_TYPE_CONFIG_APPS_NEED_TO_BE_INSTALLED_AND_UPDATED = 3;
const INSTALL_TYPE_CONFIG_INSTALLER_ERROR = 3;
export default {
  name: "App",
  data() {
    return {
      container: {
        container: null,
        axios: null,
        router: null
      },
      values: {
        appsToInstall: [],
        appsToUpdate: [],
        installType: -1
      }
    }
  },
  created() {
    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();
    this.container.container = startUp.getContainer();
    this.container.axios = this.container.container.query(AXIOS);
    this.container.router = this.container.container.query(ROUTER);

    this.container.axios.get(
        ROUTES.getInstallAppsConfiguration()
    )
        .then(
            (response) => {
              this.values.appsToInstall = response.data.appsToInstall;
              this.values.appsToUpdate = response.data.appsToUpdate;
              this.values.installType = response.data.installType;
            }
        )
  },
  methods: {
    handleClick: function () {
      switch (this.values.installType) {
        case INSTALL_TYPE_CONFIG_NOT_LOADED:
          return
        case INSTALL_TYPE_CONFIG_NOTHING_TO_BE_UPDATED:
        case INSTALL_TYPE_CONFIG_APPS_NEED_TO_BE_INSTALLED:
          this.installApps();
          break;
        case INSTALL_TYPE_CONFIG_APPS_NEED_TO_BE_UPDATED:
          console.warn('not implemented yet');
          break;
        case INSTALL_TYPE_CONFIG_APPS_NEED_TO_BE_INSTALLED_AND_UPDATED:
          console.warn('what should I do?!');
          break;
        case INSTALL_TYPE_CONFIG_INSTALLER_ERROR:
          // TODO show error
          console.error('unknown error occured ' + this.values.installType)
          break
        default:
          throw 'unknown ' + this.values.installType
      }
    },
    installApps: function () {
      this.container.axios.post(
          ROUTES.getInstallAppsAll()
      )
          .then(
              (response) => {
                this.container.router.route(response.data.routeTo)
              }
          )
          .catch(
              (response) => {
                console.error(response)
              }
          )
    }
  }
}
</script>

<style scoped>

</style>