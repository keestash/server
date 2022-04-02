<template>
  <div>
    <div class="main-wrapper login flex-grow-1">
      <div class="auth-wrapper d-flex no-block justify-content-center align-items-center"
           style="
                 background:url('/asset/img/login-background.jpg') no-repeat center center fixed;
                 -webkit-background-size: cover;
                 -moz-background-size: cover;
                 -o-background-size: cover;
                 background-size: cover;
                 ">
        <div class="auth-box">
          <div id="loginform-wrapper" class="ks-form">
            <div class="logo">
              <span class="db"><img id="logo-image" height="30px" src="/asset/img/logo_inverted_no_background.png"
                                    alt="logo"/></span>
              <h5 class="font-medium mb-3">{{ $t('login.loginToApp') }}</h5>
            </div>

            <div class="d-flex flex-column" id="demoMode" v-if="this.values.demoMode">
              <span id="sensitiveData">{{ $t('login.sensitiveData') }}</span>
              <span class="mt-3">{{ $t('login.deleteInfo') }}</span>
              <span class="mt-3">{{ $t('login.adminUser') }}</span>
              <span>{{ $t('login.adminPassword') }}</span>
            </div>


            <!-- Form -->
            <div class="row">
              <div class="col-12">
                <form class="form-horizontal mt-3" id="loginform">
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
                                            <span class="input-group-text icon-wrapper" id="password-input"><i
                                                class="fas fa-pen"></i></span>
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
                    <div class="col-xs-12 pb-3 d-flex">
                      <button
                          id="sign__in"
                          class="btn btn-primary btn-lg flex-grow-1"
                          type="submit"
                          @click="onLogin"
                          :disabled="this.loginButtonDisabled === true"
                      >{{ $t('login.signIn') }}
                      </button>
                    </div>
                  </div>

                  <div class="form-group mb-0 mt-2" v-if="this.values.registerEnabled">
                    <div class="col-sm-12 text-center">
                      {{ $t('login.createNewAccountText') }} <a :href="this.values.newAccountLink"
                                                                class="ml-1"><b>{{
                        $t('login.createNewAccountActionText')
                      }}</b></a>
                    </div>
                  </div>

                  <div class="form-group mb-0 mt-2" v-if="this.values.forgotPasswordEnabled">
                    <div class="col-sm-12 text-center">
                      {{ $t('login.forgotPasswordText') }} <a :href=getLink()
                                                              class="ml-1"><b>{{
                        $t('login.forgotPasswordActionText')
                      }}</b></a>
                    </div>
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

            <div class="alert alert-danger" role="alert" id="danger--alert" v-if="demoModal.alertVisible">
              {{ $t('login.demo.modal.alert') }}
            </div>

            <div>
              <h4>{{ $t('login.demo.modal.alert') }}</h4>
            </div>
            <div>
              {{ $t('login.demo.modal.text') }}
            </div>
            <div>
              <input
                  type="text"
                  class="form-control"
                  aria-label="Default"
                  aria-describedby="inputGroup-sizing-default"
                  placeholder="please provide your email address"
                  id="demouser_email_address"
                  v-model="demoModal.value"
              >
            </div>
            <small>
              {{ $t('login.demo.modal.info') }}
            </small>
            <div class="d-flex flex-column">
              <button
                  type="button"
                  style="background: #269dff; border-color: #269dff"
                  class="btn btn-success"
                  id="send-demo-email"
                  @click="onEmailSubmitted"
              >
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
  TEMPORARY_STORAGE
} from "../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../config/routes"
import {HEADER_NAME_TOKEN, HEADER_NAME_USER} from "../../../../../lib/js/src/Backend/Axios";

export default {
  name: "App",
  data() {
    return {
      container: {
        container: null,
        axios: null
      },
      values: {
        demo: '',
        logoPath: '',
        loginToApp: '',
        demoMode: false,
        registerEnabled: false,
        newAccountLink: '',
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
    this.container.temporaryStorage = this.container.container.query(TEMPORARY_STORAGE);
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

          if (false === this.values.demoMode) {
            return;
          }
          const modal = new bootstrap.Modal(this.$refs.emailAddressModal);
          modal.show();
        }
    )

  },
  methods: {
    getLink() {
      return $t('login.forgotPasswordLink');
    },
    onEmailSubmitted() {
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
            this.container.temporaryStorage.set("demo-submitted", "true");
            const modal = new bootstrap.Modal(this.$refs.emailAddressModal);
            modal.hide();
          }
      );

    },
    onLogin() {

      this.loginButtonDisabled = true;

      if ("" === this.models.user.trim()) {
        // _this.inputService.invalid(user);
        return;
      }

      if ("" === this.models.password.trim()) {
        // _this.inputService.invalid(password);
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

            this.container.router.routeTo(data.data.routeTo);

          })
          .catch((data) => {
            this.loginButtonDisabled = false;
            this.container.appStorage.clearAPICredentials();
            // _this.miniModal.show(
            //     'Error'
            //     , 'Ok'
            //     , 'Not Ok'
            //     , "There was an error. Please try again or contact our support"
            // );
          })
    }
  }
}
</script>

<style scoped>

</style>