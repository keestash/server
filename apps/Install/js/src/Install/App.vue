<template>
  <div>
    <div class="container" id="install__instance__container">
      <div class="container">
        <h1>{{ $t('installationHeader') }}</h1>

        <p class="lead" v-if="values.installType === 0">{{ $t('installInstructionNothingToUpdate') }}</p>
        <p class="lead" v-else-if="values.installType === 1">{{ $t('installInstructionInstallApps') }}</p>
        <p class="lead" v-else-if="values.installType === 2">{{ $t('installInstructionUpdateApps') }}</p>
        <p class="lead" v-else-if="values.installType === 3">{{ $t('installInstructionInstallUpdateApps') }}</p>
        <p class="lead" v-else-if="values.installType === 4">{{ $t('installationDescriptionError') }}</p>

        <div class="container" v-if="values.appsToInstall.length > 0">
          <div class="row" v-for="app in values.appsToInstall" v-bind:key="app.id">
            <div class="col">
              <h4 class="ui header">{{ app.name }}</h4>
              <span>Version: {{ app.versionstring }}</span>
            </div>
          </div>
        </div>


        <p class="lead" v-if="values.appsToUpdate.length > 0">{{ $t('updateInstruction') }}</p>
        <div class="container" v-if="values.appsToUpdate.length > 0">
          <div class="row" v-for="app in values.appsToUpdate" v-bind:key="app.id">
            <div class="col">
              <h4 class="ui header">{{ app.name }}</h4>
              <span>Version: {{ app.versionstring }}</span>
            </div>
          </div>
        </div>

        <button class="btn btn-primary" id="i__end__update" v-if="values.installType === 0">{{
            $t('endUpdate')
          }}
        </button>
        <button class="btn btn-primary" id="i__end__update" v-else-if="values.installType === 1">{{
            $t('installApps')
          }}
        </button>
        <button class="btn btn-primary" id="i__end__update" v-else-if="values.installType === 2">{{
            $t('updateApps')
          }}
        </button>
        <button class="btn btn-primary" id="i__end__update" v-else-if="values.installType === 3">{{
            $t('updateApps')
          }}
        </button>

      </div>

    </div>

  </div>
</template>

<script>
import {AXIOS, StartUp} from "../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../config/routes";

export default {
  name: "App",
  data() {
    return {
      container: {
        container: null,
        axios: null
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

    this.container.axios.get(
        ROUTES.getInstallAppsConfiguration()
    )
        .then(
            (response) => {
              this.values = response.data;
            }
        )
  }
}
</script>

<style scoped>

</style>