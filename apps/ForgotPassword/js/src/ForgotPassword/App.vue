<template>
  <div>
    <div class="main-wrapper forgot-password flex-grow-1">
      <div class="forgot-password-wrapper d-flex no-block justify-content-center align-items-center"
           style="
                 background:url('/asset/img/forgot-password-background.jpg') no-repeat center center fixed;
                 -webkit-background-size: cover;
                 -moz-background-size: cover;
                 -o-background-size: cover;
                 background-size: cover;
                 ">

        <div class="formbox">
          <div id="forgot-password-form" class="ks-form">
            <div class="logo">
              <span class="db"><img id="logo-image" height="30px" src="/asset/img/logo_inverted.png" alt="logo"/></span>
              <h5 class="font-medium mb-3">{{ $t('forgotPassword.loginToApp') }}</h5>
            </div>
            <!-- Form -->
            <div class="row">
              <div class="col-12">
                <form class="form-horizontal mt-3" id="forgot_password_form" @submit="onReset">
                  <div class="input-group mb-3">
                    <div class="d-flex">
                                        <span class="input-group-text icon-wrapper" id="basic-username"><i
                                            class="far fa-user"></i></span>
                    </div>
                    <input type="text"
                           id="fp__input"
                           class="form-control form-control-lg input-control"
                           :placeholder="$t('forgotPassword.usernameOrPasswordPlaceholder')"
                           :aria-label="$t('forgotPassword.usernameOrPasswordPlaceholder')"
                           v-model="emailAddress"
                           aria-describedby="basic-username"
                           :disabled="this.disabled === true"
                    >
                  </div>
                  <div class="form-group text-center">
                    <div class="col-xs-12 pb-3 d-flex">
                      <button id="fp__reset"
                              class="btn btn-primary btn-lg flex-grow-1"
                              type="submit"
                              :disabled="this.disabled === true"
                      >
                        {{ $t('forgotPassword.resetPassword') }}
                      </button>
                    </div>
                  </div>
                  <div class="form-group mb-0 mt-2">
                    <div class="col-sm-12 text-center" v-if="values.registeringEnabled">
                      {{ $t('forgotPassword.createNewAccountText') }} <a
                        :href=values.newAccountLink
                        class="ml-1"><b>{{
                        $t('forgotPassword.createNewAccountActionText')
                      }}</b></a>
                    </div>

                    <div class="col-sm-12 text-center">
                      <a
                          :href=values.backToLoginLink
                          class="ml-1"><b>{{ $t('forgotPassword.backToLogin') }} </b></a>
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
</template>

<script>
import {AXIOS, StartUp} from "../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../config/routes/index";

export default {
  name: "App",
  data() {
    return {
      container: {
        container: null,
        axios: null
      },
      values: {},
      disabled: false,
      emailAddress: ''
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
        ROUTES.getConfiguration()
    ).then(
        (response) => {
          return response.data;
        }
    ).then(
        (data) => {
          this.values = data;
        }
    );

  },
  methods: {
    onReset(e) {
      e.preventDefault();
      this.disabled = true;

      if ("" === this.emailAddress.trim()) {
        // _this.inputService.invalid(input);
        this.disabled = false;
        return;
      }

      this.container.axios.post(
          ROUTES.getForgotPasswordSubmit()
          , {
            input: this.emailAddress.trim()
          }
      )
          .then(
              (response, status, xhr) => {
                const result = response.data;

                alert(
                    {
                      header: result['header']
                      , text1: 'ok'
                      , text2: 'close'
                      , text3: result['message']
                    }
                )

                this.disabled = false;
                this.emailAddress = '';
              }
          )
          .catch(
              (response) => {
                const result = response.data;

                error(
                    {
                      header: result['header']
                      , text1: 'ok'
                      , text2: 'close'
                      , text3: result['message']
                    }
                )

                this.disabled = false;
                this.emailAddress = '';
              }
          );
    }
  }
}
</script>

<style scoped>

</style>