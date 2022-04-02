<template>
  <div class="col">
    <div class="text-center" v-if="loading.show">
      <div class="spinner-grow text-primary" role="status" v-if="loading.show">
        <span class="sr-only"></span>
      </div>
    </div>

    <form @submit="onSubmit" v-if="form.show">

      <div class="form-group">
        <label for="input-host">{{ form.content.db.host.label }}</label>
        <input
            type="text"
            class="form-control"
            id="input-host"
            aria-describedby="text"
            v-model="form.content.db.host.value"
            required
            :placeholder="form.content.db.host.placeholder"
        >
        <small class="form-text text-muted">{{ form.content.db.host.description }}</small>
      </div>

      <div class="form-group">
        <label for="input-db-user">{{ form.content.db.user.label }}</label>
        <input
            type="text"
            class="form-control"
            id="input-db-user"
            aria-describedby="text"
            v-model="form.content.db.user.value"
            required
            :placeholder="form.content.db.user.placeholder"
        >
        <small class="form-text text-muted">{{ form.content.db.user.description }}</small>
      </div>

      <div class="form-group">
        <label for="input-password">{{ form.content.db.password.label }}</label>
        <input
            type="text"
            class="form-control"
            id="input-password"
            aria-describedby="text"
            v-model="form.content.db.password.value"
            required
            :placeholder="form.content.db.password.placeholder"
        >
        <small class="form-text text-muted">{{ form.content.db.password.description }}</small>
      </div>

      <div class="form-group">
        <label for="input-db-name">{{ form.content.db.name.label }}</label>
        <input
            type="text"
            class="form-control"
            id="input-db-name"
            aria-describedby="text"
            v-model="form.content.db.name.value"
            required
            :placeholder="form.content.db.name.placeholder"
        >
        <small class="form-text text-muted">{{ form.content.db.name.description }}</small>
      </div>

      <div class="form-group">
        <label for="input-db-port">{{ form.content.db.port.label }}</label>
        <input
            type="text"
            class="form-control"
            id="input-db-port"
            aria-describedby="text"
            v-model="form.content.db.port.value"
            required
            :placeholder="form.content.db.port.placeholder"
        >
        <small class="form-text text-muted">{{ form.content.db.port.description }}</small>
      </div>

      <div class="form-group">
        <label for="input-db-charset">{{ form.content.db.charset.label }}</label>
        <input
            type="text"
            class="form-control"
            id="input-db-charset"
            aria-describedby="text"
            v-model="form.content.db.charset.value"
            required
            :placeholder="form.content.db.charset.placeholder"
        >
        <small class="form-text text-muted">{{ form.content.db.charset.description }}</small>
      </div>

      <div class="form-group">
        <label for="input-email-smtp-host">{{ form.content.email.smtp.host.label }}</label>
        <input
            type="text"
            class="form-control"
            id="input-email-smtp-host"
            aria-describedby="text"
            v-model="form.content.email.smtp.host.value"
            required
            :placeholder="form.content.email.smtp.host.placeholder"
        >
        <small class="form-text text-muted">{{ form.content.email.smtp.host.description }}</small>
      </div>

      <div class="form-group">
        <label for="input-email-smtp-user">{{ form.content.email.smtp.user.label }}</label>
        <input
            type="text"
            class="form-control"
            id="input-email-smtp-user"
            aria-describedby="text"
            v-model="form.content.email.smtp.user.value"
            required
            :placeholder="form.content.email.smtp.user.placeholder"
        >
        <small class="form-text text-muted">{{ form.content.email.smtp.user.description }}</small>
      </div>

      <div class="form-group">
        <label for="input-email-smtp-password">{{ form.content.email.smtp.password.label }}</label>
        <input
            type="text"
            class="form-control"
            id="input-email-smtp-password"
            aria-describedby="text"
            v-model="form.content.email.smtp.password.value"
            required
            :placeholder="form.content.email.smtp.password.placeholder"
        >
        <small class="form-text text-muted">{{ form.content.email.smtp.password.description }}</small>
      </div>

      <div class="form-group">
        <label for="input-log-requests">{{ form.content.logRequests.label }}</label>
        <select class="form-control" id="input-log-requests">
          <option v-for="option in form.content.logRequests.options" v-model="form.content.logRequests.value">
            {{ option }}
          </option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary">Submit</button>
    </form>

    <div class="alert-success" v-if="success.show">
      The config file is updated
    </div>
  </div>
</template>

<script>
import {AXIOS, StartUp} from "../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../config/routes";
import {RESPONSE_CODE_OK} from "../../../../../../lib/js/src/Backend/Axios";

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
              label: '',
              description: '',
              placeholder: '',
            },
            user: {
              value: 'keestash',
              label: '',
              description: '',
              placeholder: '',
            },
            password: {
              value: 'keestash',
              label: '',
              description: '',
              placeholder: '',
            },
            name: {
              value: 'keestash',
              label: '',
              description: '',
              placeholder: '',
            },
            port: {
              value: '3306',
              label: '',
              description: '',
              placeholder: '',
            },
            charset: {
              value: 'utf8mb4',
              label: '',
              description: '',
              placeholder: '',
            },
          },
          email: {
            smtp: {
              host: {
                value: 'sddsfsdf',
                label: '',
                description: '',
                placeholder: '',
              },
              user: {
                value: 'sdfsdfsdf',
                label: '',
                description: '',
                placeholder: '',
              },
              password: {
                value: 'dsfsdfs',
                label: '',
                description: '',
                placeholder: '',
              },
            }
          },
          logRequests: {
            label: '',
            value: 'enabled',
            options: ['enabled']
          }
        }
      },
      container: null
    }
  },
  computed: {
    isVisible() {
      return (this.strings || []).length > 0;
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
    ).then((response) => {
      return response.data[RESPONSE_CODE_OK]['messages'];
    })
        .then((response) => {
          const needsConfig = response.length > 0;

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

      this.form.content.db = Object.assign(
          this.form.content.db,
          this.$t('config.db')
      );

      this.form.content.email = Object.assign(
          this.form.content.email,
          this.$t('config.email')
      );

      this.form.content.logRequests = Object.assign(
          this.form.content.logRequests,
          this.$t('config.logRequests')
      );
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
          .then((r) => {
            return r.data;
          })
          .then(
              (r) => {
                if (RESPONSE_CODE_OK in r) {
                  this.makeSuccess();
                }
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