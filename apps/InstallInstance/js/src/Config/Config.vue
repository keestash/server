<template>
  <div class="container">

    <div class="text-center" v-if="loading.show">
      <div class="spinner-grow text-primary" role="status">
        <span class="sr-only"></span>
      </div>
    </div>

    <div class="row">
      <div class="col">

        <form @submit="onSubmit" v-if="form.show">

          <div class="form-group">
            <label for="input-host">{{ $t('config.db.host.label') }}</label>
            <input
                type="text"
                class="form-control"
                id="input-host"
                aria-describedby="text"
                v-model="form.content.db.host.value"
                required
                :placeholder="$t('config.db.host.placeholder')"
            >
            <small class="form-text text-muted">{{ $t('config.db.host.description') }}</small>
          </div>

          <div class="form-group">
            <label for="input-db-user">{{ $t('config.db.user.label') }}</label>
            <input
                type="text"
                class="form-control"
                id="input-db-user"
                aria-describedby="text"
                v-model="form.content.db.user.value"
                required
                :placeholder="$t('config.db.user.placeholder')"
            >
            <small class="form-text text-muted">{{ $t('config.db.user.description') }}</small>
          </div>

          <div class="form-group">
            <label for="input-password">{{ $t('config.db.password.label') }}</label>
            <input
                type="text"
                class="form-control"
                id="input-password"
                aria-describedby="text"
                v-model="form.content.db.password.value"
                required
                :placeholder="$t('config.db.password.placeholder')"
            >
            <small class="form-text text-muted">{{ $t('config.db.password.description') }}</small>
          </div>

          <div class="form-group">
            <label for="input-db-name">{{ $t('config.db.name.label') }}</label>
            <input
                type="text"
                class="form-control"
                id="input-db-name"
                aria-describedby="text"
                v-model="form.content.db.name.value"
                required
                :placeholder="$t('config.db.name.placeholder')"
            >
            <small class="form-text text-muted">{{ $t('config.db.name.description') }}</small>
          </div>

          <div class="form-group">
            <label for="input-db-port">{{ $t('config.db.port.label') }}</label>
            <input
                type="text"
                class="form-control"
                id="input-db-port"
                aria-describedby="text"
                v-model="form.content.db.port.value"
                required
                :placeholder="$t('config.db.port.placeholder')"
            >
            <small class="form-text text-muted">{{ $t('config.db.port.description') }}</small>
          </div>

          <div class="form-group">
            <label for="input-db-charset">{{ $t('config.db.charset.label') }}</label>
            <input
                type="text"
                class="form-control"
                id="input-db-charset"
                aria-describedby="text"
                v-model="form.content.db.charset.value"
                required
                :placeholder="$t('config.db.charset.placeholder')"
            >
            <small class="form-text text-muted">{{ $t('config.db.charset.description') }}</small>
          </div>

          <div class="form-group">
            <label for="input-email-smtp-host">{{ $t('config.email.smtp.host.label') }}</label>
            <input
                type="text"
                class="form-control"
                id="input-email-smtp-host"
                aria-describedby="text"
                v-model="form.content.email.smtp.host.value"
                required
                :placeholder="$t('config.email.smtp.host.placeholder')"
            >
            <small class="form-text text-muted">{{ $t('config.email.smtp.host.description') }}</small>
          </div>

          <div class="form-group">
            <label for="input-email-smtp-user">{{ $t('config.email.smtp.user.label') }}</label>
            <input
                type="text"
                class="form-control"
                id="input-email-smtp-user"
                aria-describedby="text"
                v-model="form.content.email.smtp.user.value"
                required
                :placeholder="$t('config.email.smtp.user.placeholder')"
            >
            <small class="form-text text-muted">{{ $t('config.email.smtp.user.description') }}</small>
          </div>

          <div class="form-group">
            <label for="input-email-smtp-password">{{ $t('config.email.smtp.password.label') }}</label>
            <input
                type="text"
                class="form-control"
                id="input-email-smtp-password"
                aria-describedby="text"
                v-model="form.content.email.smtp.password.value"
                required
                :placeholder="$t('config.email.smtp.password.placeholder')"
            >
            <small class="form-text text-muted">{{ $t('config.email.smtp.password.description') }}</small>
          </div>

          <div class="form-group">
            <label for="input-log-requests">{{ $t('config.logRequests.label') }}</label>
            <select class="form-control" id="input-log-requests">
              <option v-for="option in form.content.logRequests.options"
                      v-bind:value="{ option: form.content.logRequests.value }">
                {{ option }}
              </option>
            </select>
          </div>

          <button type="submit" class="btn btn-primary">{{ $t('config.submit.label') }}</button>
        </form>
      </div>
    </div>

    <div class="row">
      <div class="col">
        <div class="alert alert-success" role="alert" v-if="success.show">
          {{ $t('config.nothingToUpdate') }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {AXIOS, StartUp} from "../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../config/routes";

export default {
  name: "Config",
  data() {
    return {
      strings: [],
      success: {
        show: false
      },
      loading: {
        show: true
      },
      form: {
        show: false,
        content: {
          db: {
            host: {
              value: 'localhost',
            },
            user: {
              value: 'keestash',
            },
            password: {
              value: 'keestash',
            },
            name: {
              value: 'keestash',
            },
            port: {
              value: '3306',
            },
            charset: {
              value: 'utf8mb4',
            },
          },
          email: {
            smtp: {
              host: {
                value: 'sddsfsdf',
              },
              user: {
                value: 'sdfsdfsdf',
              },
              password: {
                value: 'dsfsdfs',
              },
            }
          },
          logRequests: {
            value: 'enabled',
            options: ['enabled', 'disabled']
          }
        }
      },
      container: null
    }
  },
  async created() {
    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();

    this.container = startUp.getContainer();
    const axios = this.container.query(AXIOS);

    axios.request(
        ROUTES.GET_INSTALL_INSTANCE_CONFIG_DATA()
    )
        .then((response) => {
          const needsConfig = response.data.length > 0;

          if (true === needsConfig) {
            this.showForm();
            return;
          }
          this.makeSuccess();
        })

  },
  methods: {
    async showForm() {
      this.form.show = true;
      this.loading.show = false;
      this.success.show = false;
    },
    makeSuccess() {
      this.form.show = false;
      this.loading.show = false;
      this.success.show = true;
      this.$emit('configFinished', true);
    },
    onSubmit(event) {
      event.preventDefault();
      this.loading.show = true;
      const value = {
        host: this.form.content.db.host.value
        , user: this.form.content.db.user.value
        , password: this.form.content.db.password.value
        , schema_name: this.form.content.db.name.value
        , port: this.form.content.db.port.value
        , charset: this.form.content.db.charset.value
        , log_requests: this.form.content.logRequests.value
        , smtp_host: this.form.content.email.smtp.host.value
        , smtp_user: this.form.content.email.smtp.user.value
        , smtp_password: this.form.content.email.smtp.password.value
      };

      const axios = this.container.query(AXIOS);
      axios.post(
          ROUTES.INSTALL_INSTANCE_UPDATE_CONFIG(),
          value
      )
          .then(
              (r) => {
                this.makeSuccess();
              }
          ).catch(
          (r) => {
            console.error(r)
          }
      )
    }
  }
}
</script>

<style scoped>

</style>