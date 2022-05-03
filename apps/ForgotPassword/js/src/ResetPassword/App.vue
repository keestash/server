<template>
  <div>
    <div class="reset-password main-wrapper flex-grow-1">
      <div class="reset-password-wrapper d-flex no-block justify-content-center align-items-center"
           style="background:url('/asset/img/login-background.jpg') no-repeat center center;">
        <div class="formbox">
          <div id="reset-password-form">
            <div class="logo">
              <span class="db"><img id="logo-image" height="30px" src="/asset/img/logo_inverted.png" alt="logo"/></span>
              <h5 class="font-medium mb-3">{{ $t('resetPassword.title') }}</h5>
            </div>
            <!-- Form -->
            <div class="row" v-if="values.hasHash">
              <div class="col-12">
                <form class="form-horizontal mt-3" id="reset_password_form" @submit="onSubmit">
                  <div class="input-group mb-3">
                    <div class="d-flex">
                                        <span class="input-group-text" id="basic-addon1"><i
                                            class="fas fa-pen"></i></span>
                    </div>
                    <input type="text"
                           id="rp__input"
                           class="form-control form-control-lg"
                           :data-token="values.token"
                           :placeholder="$t('resetPassword.passwordLabel')"
                           :aria-label="$t('resetPassword.passwordLabel')" aria-describedby="basic-addon1"
                           :disabled="this.formDisabled === true"
                           v-model="newPassword"
                    >
                  </div>
                  <div class="form-group text-center">
                    <div class="col-xs-12 pb-3">
                      <button id="rp__reset"
                              class="btn btn-block btn-lg btn-info d-flex justify-content-center align-items-center"
                              type="submit">
                        {{ $t('resetPassword.resetPassword') }}
                        <div class="spinner-border spinner-border-sm invisible" id="rp__spinner"
                             role="status"></div>
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
            <div class="row" v-else>
              <div class="col-12 d-flex justify-content-center">
                <h5 class="font-medium mb-3">{{ $t('resetPassword.noHashFound') }}</h5>
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
      values: {
        hasHash: true,
        token: null
      },
      formDisabled: false,
      newPassword: '',
      token: null
    }
  },
  created() {
    const el = document.getElementById('rp-data-node');
    this.token = el.dataset.token;
    el.remove();

    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();
    this.container.container = startUp.getContainer();
    this.container.axios = this.container.container.query(AXIOS);

    this.container.axios.get(
        ROUTES.getAccountDetails(this.token)
    ).then(
        (response) => {
          return response.data;
        }
    ).then(
        (data) => {
          this.values = data;
        }
    )
  },
  methods: {
    onSubmit(e) {
      e.preventDefault();

      this.formDisabled = true;

      if ("" === this.newPassword.trim()) {
        // _this.inputService.invalid(input);
        this.formDisabled = false;
        return;
      }

      if (null === this.token || "" === this.token.trim()) {
        this.formDisabled = false;
        return;
      }

      // TODO check for minimum requirements

      this.container.axios.post(
          ROUTES.getResetPasswordSubmit()
          , {
            input: this.newPassword
            , hash: this.token
          }
      ).then(
          (response) => {
            let result = null;

            alert(
                JSON.stringify(
                    [result['header']
                      , 'ok'
                      , 'close'
                      , result['message']]
                )
            )
            this.formDisabled = false;
            this.newPassword = '';
          }
      ).catch(
          () => {
            alert(
                JSON.stringify(
                    [
                      'password reset'
                      , 'ok'
                      , 'close'
                      , "There was an error. Please try again or contact our support"
                    ]
                )
            )

            this.formDisabled = false;
          }
      )
    }
  }

}
</script>

<style scoped>

</style>