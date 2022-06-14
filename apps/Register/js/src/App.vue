<template>
  <div>
    <div class="main-wrapper register">
      <div class="register-wrapper d-flex no-block justify-content-center align-items-center">
        <form class="register-box" @submit="onRegister">
          <div class="logo">
            <span class="db">
                <img id="logo-image"
                     src="../img/logo_inverted_no_background.png"
                     alt="logo"/>
            </span>
            <h5 class="font-medium mb-3">{{ $t('createNewAccount') }}</h5>
            <span>{{ $t('createNewAccountDesc') }}</span>
          </div>

          <div class="form-group input-group">
            <div class="input-group-prepend">
              <span class="input-group-text">
                  <i class="fa fa-user"></i>
              </span>
            </div>
            <input
                class="form-control"
                :placeholder="$t('firstNamePlaceholder')"
                :aria-label="$t('firstNamePlaceholder')"
                v-model="form.firstName"
                type="text"
                @input="this.registerButtonDisabled = this.registerButtonDisabled || (this.form.firstName.length === 0);"
                required
            >
          </div>

          <div class="form-group input-group">
            <div class="input-group-prepend">
              <span class="input-group-text">
                  <i class="fa fa-user"></i>
              </span>
            </div>
            <input
                class="form-control"
                :placeholder="$t('lastNamePlaceholder')"
                :aria-label="$t('lastNamePlaceholder')"
                type="text"
                v-model="form.lastName"
                @input="this.registerButtonDisabled = this.registerButtonDisabled || (this.form.lastName.length === 0);"
                required
            >
          </div>

          <div class="form-group input-group">
            <div class="input-group-prepend">
              <span class="input-group-text">
                  <i class="fa fa-user"></i>
              </span>
            </div>
            <input
                class="form-control"
                :placeholder="$t('userNamePlaceholder')"
                :aria-label="$t('userNamePlaceholder')"
                type="text"
                v-model="form.userName"
                @input="this.registerButtonDisabled = this.registerButtonDisabled || (this.form.userName.length === 0);"
                required
            >
          </div>

          <div class="form-group input-group">
            <div class="input-group-prepend">
              <span class="input-group-text">
                  <i class="fa fa-envelope"></i>
              </span>
            </div>
            <input
                class="form-control"
                :placeholder="$t('emailPlaceholder')"
                :aria-label="$t('emailPlaceholder')"
                type="email"
                v-model="form.email"
                @input="this.registerButtonDisabled = this.registerButtonDisabled || (this.form.email.length === 0);"
                required
            >
          </div>

          <div class="form-group input-group">
            <div class="input-group-prepend">
              <span class="input-group-text">
                  <i class="fa fa-phone"></i>
              </span>
            </div>
            <select class="custom-select"
                    v-model="form.phone.prefix"
            >
              <option selected=""></option>
              <option v-for="(code, prefix) in configuration.phoneConfig" value="{{code}}">{{ code }} ({{
                  prefix
                }})
              </option>
            </select>
            <input
                class="form-control"
                :placeholder="$t('phonePlaceholder')"
                :aria-label="$t('phonePlaceholder')"
                v-model="form.phone.number"
                type="text"
            >
          </div>

          <div class="form-group input-group">
            <div class="input-group-prepend">
              <span class="input-group-text">
                  <i class="fab fa-chrome"></i>
              </span>
            </div>
            <input
                class="form-control"
                :placeholder="$t('websitePlaceholder')"
                :aria-label="$t('websitePlaceholder')"
                type="text"
                v-model="form.website"
                @input="this.registerButtonDisabled = this.registerButtonDisabled || (this.form.website.length === 0);"
            >
          </div>

          <div class="form-group input-group" id="register-password-field" aria-describedby="tooltip">
            <div class="input-group-prepend">
              <span class="input-group-text">
                  <i class="fa fa-lock"></i>
              </span>
            </div>
            <input :type="isShown ? 'text' : 'password'"
                   class="form-control"
                   :value="form.password"
                   autocomplete="off"
                   :placeholder="$t('passwordLabel')"
                   :aria-label="$t('passwordLabel')"
                   v-model="form.password"
                   @input="onPasswordChange"
                   required
            >
            <div class="input-group-append" id="pwm__password__eye" @click="this.isShown = !this.isShown;">
              <div class="input-group-text">
                <i class="fas fa-eye"></i>
              </div>
            </div>

            <div id="register-password-field-tooltip" class="row" role="tooltip">
              <div class="col">
                <ul class="list-group">
                  <li class="list-group-item d-flex justify-content-start align-items-start pt-1 pb-1">
                    <small :class="passwordHints.hasEightCharacters ? 'olivegreen' : 'darkred'">At least 8
                      characters</small>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center pt-1 pb-1">
                    <small :class="passwordHints.hasLowerCase ? 'olivegreen' : 'darkred'">At least one lower
                      case character</small>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center pt-1 pb-1">
                    <small :class="passwordHints.hasUpperCase ? 'olivegreen' : 'darkred'">At least one upper
                      case character</small>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center pt-1 pb-1">
                    <small :class="passwordHints.hasSpecialChar ? 'olivegreen' : 'darkred'">At least one
                      special character</small>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center pt-1 pb-1">
                    <small :class="passwordHints.hasOneNumber ? 'olivegreen' : 'darkred'">At least one
                      number</small>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          {{ form.termsAndConditions }}
          {{ this.registerButtonDisabled }}
          <div class="input-group mb-3">
            <div class="form-check" v-if="configuration.loading===false">
              <input
                  type="checkbox"
                  class="form-check-input"
                  required
                  v-model="form.termsAndConditions"
                  @input="this.registerButtonDisabled = this.registerButtonDisabled || (true === this.form.termsAndConditions);"
              >
              <label
                  class="form-check-label"
                  for="terms_and_conditions"
              > {{ $t('termsConditionsFirstPart') }}
                <a
                    :href="this.configuration.tncLink"
                    target="_blank"
                >{{ $t('termsAndConditions') }}
                </a>
              </label>
            </div>
            <IsLoading class="d-flex is-loading" v-else></IsLoading>
          </div>

          <div class="form-group">
            <button type="submit"
                    class="btn btn-primary btn-block"
                    :disabled="this.registerButtonDisabled === true"
            >
              {{ $t('submit') }}
            </button>
          </div>

          <p class="text-center" v-if="configuration.loading===false">
            {{ $t('backToLoginQuestion') }}
            <a
                :href="this.configuration.loginLink"
                target="_blank"
            >{{ $t('backToLogin') }}
            </a>
          </p>
          <IsLoading class="d-flex is-loading" v-else></IsLoading>
        </form>
      </div>
    </div>

    <Modal
        :open="addedModalOpened"
        :has-description="false"
        @saved="this.addedModalOpened=false"
        @closed="this.addedModalOpened=false"
        :has-positive-button="true"
        :has-negative-button="false"
        unique-id="register-added-modal"
    >
      <template v-slot:title>{{ $t('modal.success.title') }}</template>
      <template v-slot:body-description></template>
      <template v-slot:body>{{ $t('modal.success.body') }}</template>
      <template v-slot:button-text>{{ $t('modal.success.buttonText') }}</template>
      <template v-slot:negative-button-text></template>
    </Modal>

    <Modal
        :open="errorModalOpened"
        :has-description="false"
        @saved="this.errorModalOpened=false"
        @closed="this.errorModalOpened=false"
        :has-positive-button="true"
        :has-negative-button="false"
        unique-id="register-error-modal"
    >
      <template v-slot:title>
        <div class="container text-center darkred">
          <div class="row">
            <div class="col">
              <i class="fa fa-times"></i>
            </div>
          </div>
          <div class="row">
            <div class="col">
              {{ $t('modal.error.title') }}
            </div>
          </div>
        </div>
      </template>
      <template v-slot:body-description></template>
      <template v-slot:body>{{ $t('modal.error.body') }}</template>
      <template v-slot:button-text>{{ $t('modal.error.buttonText') }}</template>
      <template v-slot:negative-button-text></template>
    </Modal>
  </div>
</template>

<script>
import {AXIOS, StartUp} from "../../../../lib/js/src/StartUp";
import {Container} from "../../../../lib/js/src/DI/Container";
import {ROUTES} from "./../config/routes/index";
import Modal from "../../../../lib/js/src/Components/Modal";
import {createPopper} from '@popperjs/core';
import IsLoading from "../../../../lib/js/src/Components/IsLoading";

export default {
  name: "App",
  components: {IsLoading, Modal},
  created() {
    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();
    this.container.container = startUp.getContainer();
    this.container.axios = this.container.container.query(AXIOS);

    this.container.axios.get(
        ROUTES.getConfiguration()
    )
        .then(
            (response) => {
              this.configuration = response.data;
              this.configuration.loading = false;
            }
        );

  },

  mounted() {
    const popcorn = document.querySelector('#register-password-field');
    const tooltip = document.querySelector('#register-password-field-tooltip');

    createPopper(popcorn, tooltip, {
      placement: 'right',
      modifiers: [
        {
          name: 'offset',
          options: {
            offset: [0, 8],
          },
        },
      ],
    });

  },
  data() {
    return {
      passwordHints: {
        hasEightCharacters: false,
        hasLowerCase: false,
        hasUpperCase: false,
        hasSpecialChar: false,
        hasOneNumber: false,
      },
      isShown: false,
      addedModalOpened: false,
      errorModalOpened: false,
      configuration: {
        phoneConfig: [],
        tncLink: '',
        loginLink: '',
        loading: true
      },
      container: {
        container: null,
        axios: null
      },
      registerButtonClicked: false,
      registerButtonDisabled: false,
      form: {
        firstName: 'Dogan',
        lastName: 'Ucar',
        userName: 'dogano',
        password: 'Dogancan1@',
        phone: {
          prefix: '+49',
          value: '15755704076'
        },
        email: 'dogan@dogan-ucar.de',
        website: 'dogan-ucar.de',
        termsAndConditions: true
      }
    }
  },
  methods: {
    onPasswordChange() {
      this.passwordHints.hasEightCharacters = this.form.password.length > 8;
      this.passwordHints.hasLowerCase = /[a-z]/.test(this.form.password);
      this.passwordHints.hasUpperCase = /[A-Z]/.test(this.form.password);
      this.passwordHints.hasOneNumber = /[0-9]/.test(this.form.password);
      this.passwordHints.hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/.test(this.form.password);

      this.registerButtonDisabled = this.registerButtonClicked || !(
          this.passwordHints.hasEightCharacters
          && this.passwordHints.hasLowerCase
          && this.passwordHints.hasUpperCase
          && this.passwordHints.hasOneNumber
          && this.passwordHints.hasSpecialChar
      );
    },
    onRegister(event) {
      event.preventDefault();

      if (true === this.registerButtonClicked) return;
      this.registerButtonClicked = true;

      this.container.axios.post(
          ROUTES.getRegisterAdd()
          , {
            'first_name': this.form.firstName
            , 'last_name': this.form.lastName
            , 'user_name': this.form.userName
            , 'email': this.form.email
            , 'phone': this.form.phone.prefix + this.form.phone.number
            , 'website': this.form.website
            , 'password': this.form.password
            , 'terms_and_conditions': this.form.termsAndConditions
          }
      )
          .then(
              () => {
                this.addedModalOpened = true;
                this.registerButtonClicked = false;
                this.form.firstName = '';
                this.form.lastName = '';
                this.form.userName = '';
                this.form.email = '';
                this.form.website = '';
                this.form.phone.prefix = '';
                this.form.phone.value = '';
                this.form.password = '';
                this.form.termsAndConditions = false;
              }
          )
          .catch(
              (error) => {
                this.errorModalOpened = true;
                this.registerButtonClicked = false;
                console.log(error.response)
              }
          )
    }
  }
}
</script>

<style scoped lang="scss">

.register {
  .register-wrapper {
    min-height: 100vh;
    position: relative;
    background: url('./../img/register-background.jpg') no-repeat center center fixed;
    -webkit-background-size: cover;
    -moz-background-size: cover;
    -o-background-size: cover;
    background-size: cover;

    .register-box {
      background: transparent;
      padding: 20px;
      max-width: 800px;
      margin: 5% 0;

      .custom-select {
        max-width: 120px;
      }

      .logo {
        text-align: center;
        margin-bottom: 5vh;

        #logo-image {
          height: 10rem;
        }

      }
    }
  }

  .is-loading {
    width: 100%;
    height: 2rem;
  }

  #register-password-field-tooltip {
    opacity: 0.9;
  }

}
</style>