<template>
  <div class="col">
    <h4>{{ this.head.value }}</h4>
    <div class="text-center" v-if="loading.show">
      <b-spinner type="grow" variant="primary" label="Spinning" v-if="loading.show"></b-spinner>
    </div>

    <b-form @submit="onSubmit" v-if="form.show">
      <b-form-group
          id="input-group-host"
          :label="form.content.db.host.label"
          label-for="input-host"
          :description="form.content.db.host.description"
      >
        <b-form-input
            id="input-host"
            v-model="form.content.db.host.value"
            type="text"
            required
            :placeholder="form.content.db.host.placeholder"
        ></b-form-input>
      </b-form-group>

      <b-form-group
          id="input-group-db-user"
          :label="form.content.db.user.label"
          label-for="input-db-user"
          :description="form.content.db.user.description"
      >
        <b-form-input
            id="input-db-user"
            v-model="form.content.db.user.value"
            type="text"
            required
            :placeholder="form.content.db.user.placeholder"
        ></b-form-input>
      </b-form-group>

      <b-form-group
          id="input-group-password"
          :label="form.content.db.password.label"
          label-for="input-password"
          :description="form.content.db.password.description"
      >
        <b-form-input
            id="input-password"
            v-model="form.content.db.password.value"
            type="text"
            required
            :placeholder="form.content.db.password.placeholder"
        ></b-form-input>
      </b-form-group>

      <b-form-group
          id="input-group-db-name"
          :label="form.content.db.name.label"
          label-for="input-db-name"
          :description="form.content.db.name.description"
      >
        <b-form-input
            id="input-db-name"
            v-model="form.content.db.name.value"
            type="text"
            required
            :placeholder="form.content.db.name.placeholder"
        ></b-form-input>
      </b-form-group>

      <b-form-group
          id="input-group-db-port"
          :label="form.content.db.port.label"
          label-for="input-db-port"
          :description="form.content.db.port.description"
      >
        <b-form-input
            id="input-db-port"
            v-model="form.content.db.port.value"
            type="text"
            required
            :placeholder="form.content.db.port.placeholder"
        ></b-form-input>
      </b-form-group>

      <b-form-group
          id="input-group-db-charset"
          :label="form.content.db.charset.label"
          label-for="input-db-charset"
          :description="form.content.db.charset.description"
      >
        <b-form-input
            id="input-db-charset"
            v-model="form.content.db.charset.value"
            type="text"
            required
            :placeholder="form.content.db.charset.placeholder"
        ></b-form-input>
      </b-form-group>

      <b-form-group
          id="input-group-email-smtp-host"
          :label="form.content.email.smtp.host.label"
          label-for="input-email-smtp-host"
          :description="form.content.email.smtp.host.description"
      >
        <b-form-input
            id="input-email-smtp-host"
            v-model="form.content.email.smtp.host.value"
            type="text"
            required
            :placeholder="form.content.email.smtp.host.placeholder"
        ></b-form-input>
      </b-form-group>

      <b-form-group
          id="input-group-email-smtp-user"
          :label="form.content.email.smtp.user.label"
          label-for="input-email-smtp-user"
          :description="form.content.email.smtp.user.description"
      >
        <b-form-input
            id="input-email-smtp-host"
            v-model="form.content.email.smtp.user.value"
            type="text"
            required
            :placeholder="form.content.email.smtp.user.placeholder"
        ></b-form-input>
      </b-form-group>

      <b-form-group
          id="input-group-email-smtp-password"
          :label="form.content.email.smtp.password.label"
          label-for="input-email-smtp-password"
          :description="form.content.email.smtp.password.description"
      >
        <b-form-input
            id="input-email-smtp-password"
            v-model="form.content.email.smtp.password.value"
            type="text"
            required
            :placeholder="form.content.email.smtp.password.placeholder"
        ></b-form-input>
      </b-form-group>

      <b-form-group
          id="input-group-log-requests"
          :label="form.content.logRequests.label"
          label-for="input-log-requests"
      >
        <b-form-select
            id="input-log-requests"
            v-model="form.content.logRequests.value"
            :options="form.content.logRequests.options"
            required
        ></b-form-select>
      </b-form-group>

      <b-button type="submit" variant="primary">Submit</b-button>
    </b-form>

    <div class="alert-success" v-if="success.show">
      The config file is updated
    </div>
  </div>
</template>

<script>
import {ASSET_READER, AXIOS, StartUp} from "../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../config/routes";
import {RESPONSE_CODE_OK} from "../../../../../../lib/js/src/Backend/Axios";

export default {
  name: "Config",
  data() {
    return {
      head: {
        value: ''
      },
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
      return this.strings.length > 0;
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

      const assetReader = this.container.query(ASSET_READER);
      const assets = await assetReader.read(true);
      const strings = JSON.parse(assets[1].install_instance).strings;

      this.head.value = strings.config.header;

      this.form.content.db = Object.assign(
          this.form.content.db,
          strings.config.db,
      );

      this.form.content.email = Object.assign(
          this.form.content.email,
          strings.config.email,
      );

      this.form.content.logRequests = Object.assign(
          this.form.content.logRequests,
          strings.config.logRequests,
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