<template>
  <div>
    <div class="main-wrapper login flex-grow-1">
      <div class="auth-wrapper d-flex no-block justify-content-center align-items-center">
        <div class="auth-box">
          <div class="ks-form">
            <div class="logo">
              <span class="db">
                  <img id="logo-image" src="../../img/logo_inverted_no_background.png" alt="logo"/>
              </span>
              <h5 class="font-medium mb-3">{{ $t('login.loginToApp') }}</h5>
            </div>

            <div class="d-flex flex-column" id="demoMode" v-if="this.values.demoMode">
              <span id="sensitiveData">{{ $t('login.demo.sensitiveData') }}</span>
              <span class="mt-3">{{ $t('login.demo.deleteInfo') }}</span>
            </div>

            <div class="row mt-2" v-if="this.values.demoMode">
              <div class="col">
                {{ $t('login.adminPassword') }}
              </div>
            </div>
            <div class="row mb-2" v-if="this.values.demoMode">
              <div class="col">
                {{ $t('login.adminUser') }}
              </div>
            </div>

            <div class="row">
              <div class="col-12">
                <form>
                  <div class="input-group mb-3">
                    <div class="d-flex">
                                            <span class="icon-wrapper input-group-text" id="username-input"><i
                                                class="far fa-user"></i></span>
                    </div>
                    <input type="text"
                           id="username"
                           class="form-control form-control-lg input-control"
                           :placeholder="$t('login.userNamePlaceholder')"
                           :aria-label="$t('login.userNamePlaceholder')"
                           aria-describedby="username-input"
                           v-model="models.user"
                    >
                  </div>
                  <div class="input-group mb-3">
                    <div class="d-flex">
                        <span class="input-group-text icon-wrapper" id="password-input">
                            <i class="fas fa-pen"></i>
                        </span>
                    </div>
                    <input type="password"
                           id="password"
                           class="form-control form-control-lg input-control"
                           :placeholder="$t('login.passwordPlaceholder')"
                           :aria-label="$t('login.passwordPlaceholder')"
                           aria-describedby="password-input"
                           autocomplete="off"
                           v-model="models.password"
                    >
                  </div>
                  <div class="form-group text-center">
                    <div class="col-xs-12 d-flex">
                      <button
                          class="btn btn-primary btn-lg flex-grow-1"
                          type="submit"
                          @click="onLogin"
                          :disabled="this.configuration.loaded && this.loginButtonDisabled === true"
                      >{{ $t('login.signIn') }}
                      </button>
                    </div>
                  </div>

                  <div class="form-group mb-0 mt-2 text-center">

                    <div v-if="configuration.loaded && this.values.registerEnabled">
                      {{ $t('login.createNewAccountText') }}
                      <a :href="this.values.newAccountLink" class="ml-1">
                        <b>
                          {{ $t('login.createNewAccountActionText') }}
                        </b>
                      </a>
                    </div>

                  </div>

                  <div class="form-group mb-0 mt-2 text-center">

                    <div v-if="configuration.loaded && this.values.registerEnabled">
                      {{ $t('login.forgotPasswordText') }}
                      <a :href="this.values.forgotPasswordLink" class="ml-1">
                        <b>
                          {{ $t('login.forgotPasswordActionText') }}
                        </b>
                      </a>
                    </div>

                  </div>

                  <div class="form-group mb-0 mt-2 text-center">
                    <div class="spinner-grow text-primary spinner-border-sm" role="status"
                         v-if="!configuration.loaded"></div>
                  </div>

                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div
        class="modal"
        ref="emailAddressModal"
        tabindex="-1"
        role="dialog"
        aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true"
        data-keyboard="false"
        data-focus="true"
        data-backdrop="static"
    >
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-body d-flex flex-column">
            <div class="container-fluid">
              <div class="row">
                <div class="col">
                  <h4>{{ $t('login.demo.modal.title') }}</h4>
                </div>
              </div>
              <div class="row">
                <div class="col">
                  {{ $t('login.demo.modal.text') }}
                </div>
              </div>
              <div class="row mb-2">
              </div>
              <div class="row">
                <div class="col">
                  <form>
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col">
                          <label for="e-mail-address" class="form-label">{{
                              $t('login.demo.modal.input.label')
                            }}</label>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col">
                          <input
                              type="email"
                              class="form-control"
                              id="e-mail-address"
                              aria-describedby="e-mail-address-description"
                              v-model="demoModal.value"
                          >
                          <div id="e-mail-address-description" class="form-text small">
                            {{ $t('login.demo.modal.input.description') }}
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col">
                          <div class="alert alert-danger" role="alert" id="danger--alert"
                               v-if="demoModal.alertVisible">
                            {{ $t('login.demo.modal.alert') }}
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col">
                          <button type="submit" class="btn btn-primary" @click="onEmailSubmitted">
                            {{ $t('login.demo.modal.sendButton') }}
                          </button>
                          <div
                              class="spinner-border flex-grow-1 align-self-center"
                              role="status"
                              id="loading--spinner"
                              v-if="demoModal.loadingSpinnerVisible"
                          >
                        <span class="sr-only">
                            {{ $t('login.demo.modal.loadingInfo') }}
                        </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>


          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {
  APP_STORAGE,
  AXIOS,
  EMAIL_VALIDATOR,
  ROUTER,
  StartUp,
} from "../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../config/routes/index"
import {HEADER_NAME_TOKEN, HEADER_NAME_USER} from "../../../../../lib/js/src/Backend/Axios";
import {Modal} from "bootstrap";
import IconModal from "../../../../../lib/js/src/UI/IconModal";

export default {
  name: "App",
  components: {IconModal},
  data() {
    return {
      container: {
        container: null,
        axios: null
      },
      configuration: {
        loaded: false
      },
      values: {
        demo: '',
        logoPath: '',
        loginToApp: '',
        demoMode: false,
        registerEnabled: false,
        newAccountLink: '',
        forgotPasswordLink: '',
        forgotPasswordEnabled: false,
      },
      models: {
        user: '',
        password: ''
      },
      loginButtonDisabled: false,
      demoModal: {
        alertVisible: false,
        loadingSpinnerVisible: false,
        value: ''
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
    this.container.appStorage = this.container.container.query(APP_STORAGE);
    this.container.router = this.container.container.query(ROUTER);
    this.container.emailValidator = this.container.container.query(EMAIL_VALIDATOR);

    this.container.axios.get(
        ROUTES.getAppConfiguration()
    ).then(
        (response) => {
          return response.data;
        }
    ).then(
        (data) => {
          this.values = data;

          this.configuration.loaded = true;
          if (false === this.values.demoMode) {
            return;
          }

          if (this.isDemoUserSubmitted()) {
            return;
          }

          const modal = new Modal(this.$refs.emailAddressModal);
          modal.show();
        }
    )

    this.showModal = true

  },
  methods: {
    closeModal() {
      this.isModalVisible = false;
    },
    isDemoUserSubmitted() {
      return this.$store.getters.emailSubmitted;
    },
    onEmailSubmitted(e) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();

      this.demoModal.alertVisible = false;
      const email = this.demoModal.value;

      if ("" === email || false === this.container.emailValidator.isValidAddress(email)) {
        this.demoModal.alertVisible = true;
        return;
      }

      this.demoModal.loadingSpinnerVisible = true;
      this.container.axios.post(
          ROUTES.getDemoUsersAdd()
          , {
            email: email
          }
      ).then(
          () => {
            this.$store.dispatch("setEmailSubmitted", true);
            const modal = new Modal(this.$refs.emailAddressModal);
            modal.hide();
          }
      );

    },
    onLogin(e) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();

      this.loginButtonDisabled = true;

      if ("" === this.models.user.trim()) {
        this.loginButtonDisabled = false;
        return;
      }

      if ("" === this.models.password.trim()) {
        this.loginButtonDisabled = false;
        return;
      }

      this.container.axios.post(
          ROUTES.getLoginSubmit()
          , {
            'user': this.models.user.trim()
            , 'password': this.models.password.trim()
          }
      )
          .then((response) => {
            return {
              data: response.data
              , headers: {
                [HEADER_NAME_TOKEN]: response.headers[HEADER_NAME_TOKEN]
                , [HEADER_NAME_USER]: response.headers[HEADER_NAME_USER]
              }
            };
          })
          .then((data) => {
            this.loginButtonDisabled = false;
            if (0 === data.length) {
              this.container.appStorage.clearAPICredentials();
              return;
            }

            this.container.appStorage.storeAPICredentials(
                data.headers[HEADER_NAME_TOKEN]
                , data.headers[HEADER_NAME_USER]
            );

            this.container.appStorage.storeLocale(data.data.settings.locale);
            this.container.appStorage.storeLanguage(data.data.settings.language);

            this.$store.dispatch(
                "setApiCredentials"
                , {
                  token: data.headers[HEADER_NAME_TOKEN]
                  , user: data.headers[HEADER_NAME_USER]
                }
            );

            this.$store.dispatch(
                "setI18n"
                , {
                  locale: data.data.settings.locale
                  , language: data.data.settings.language
                }
            );

            this.container.router.routeTo(data.data.routeTo);

          })
          .catch((data) => {
            this.loginButtonDisabled = false;
            this.container.appStorage.clearAPICredentials();
          })
    }
  }
}
</script>

<style scoped lang="scss">
#app-content {
  padding: 0 !important;
}

.login {

  .auth-wrapper {
    min-height: 100vh;
    position: relative;
    background: url('./../../img/login-background.jpg') no-repeat center center fixed;
    -webkit-background-size: cover;
    -moz-background-size: cover;
    -o-background-size: cover;
    background-size: cover;

    .auth-box {
      background: rgba(255, 255, 255, 0.4);
      padding: 20px;
      max-width: 400px;
      box-shadow: 0 1px 4px 0 rgba(0, 0, 0, .1);
      width: 90%;
      margin: 10% 0;

      #demoMode {
        #sensitiveData {
          color: red;
        }
      }

      .logo {
        text-align: center;

        #logo-image {
          height: 5rem;
        }

      }
    }

  }

}

</style>