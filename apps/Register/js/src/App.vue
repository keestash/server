<template>
  <div>
    <div class="main-wrapper register">
      <div class="register-wrapper d-flex no-block justify-content-center align-items-center"
           style="
                 background:url('/asset/img/register-background.jpg') no-repeat center center fixed;
                 -webkit-background-size: cover;
                 -moz-background-size: cover;
                 -o-background-size: cover;
                 background-size: cover;
                 ">
        <form class="register-box" id="register--box" @submit="onRegister">
          <div class="logo">
            <span class="db"><img id="logo-image" height="30px" src="/asset/img/logo_inverted_no_background.png"
                                  alt="logo"/></span>
            <h5 class="font-medium mb-3">{{ $t('createNewAccount') }}</h5>
            <span>{{ $t('createNewAccountDesc') }}</span>
          </div>

          <div class="form-group input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"> <i class="fa fa-user"></i> </span>
            </div>
            <input
                id="first_name"
                name="first_name"
                class="form-control"
                :placeholder="$t('firstNamePlaceholder')"
                :aria-label="$t('firstNamePlaceholder')"
                v-model="form.firstName"
                type="text" required
            >
          </div> <!-- form-group// -->
          <div class="form-group input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"> <i class="fa fa-user"></i> </span>
            </div>
            <input
                name="last_name"
                id="last_name"
                class="form-control"
                :placeholder="$t('lastNamePlaceholder')"
                :aria-label="$t('lastNamePlaceholder')"
                type="text"
                v-model="form.lastName"
                required>
          </div> <!-- form-group// -->
          <div class="form-group input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"> <i class="fa fa-user"></i> </span>
            </div>
            <input
                name="user_name"
                id="user_name"
                class="form-control"
                :placeholder="$t('userNamePlaceholder')"
                :aria-label="$t('userNamePlaceholder')"
                type="text"
                v-model="form.userName"
                required
            >
          </div> <!-- form-group// -->
          <div class="form-group input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"> <i class="fa fa-envelope"></i> </span>
            </div>
            <input
                name="email"
                id="email"
                class="form-control"
                :placeholder="$t('emailPlaceholder')"
                :aria-label="$t('emailPlaceholder')"
                type="email"
                v-model="form.email"
                required>
          </div> <!-- form-group// -->
          <div class="form-group input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"> <i class="fa fa-phone"></i> </span>
            </div>
            <select class="custom-select" id="phone_prefix" name="phone_prefix" style="max-width: 120px;"
                    v-model="form.phone.prefix">
              <option selected="">+49</option>
              <option value="1">+90</option>
              <option value="2">+44</option>
              <!-- TODO add more -->
            </select>
            <input
                name="phone"
                id="phone"
                class="form-control"
                :placeholder="$t('phonePlaceholder')"
                :aria-label="$t('phonePlaceholder')"
                v-model="form.phone.number"
                type="text"
            >
          </div> <!-- form-group// -->
          <div class="form-group input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"> <i class="fab fa-chrome"></i> </span>
            </div>
            <input
                name="website"
                id="website"
                class="form-control"
                :placeholder="$t('websitePlaceholder')"
                aria-label="$t('websitePlaceholder')"
                type="text"
                v-model="form.website"
            >
          </div> <!-- form-group// -->
          <div class="form-group input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"> <i class="fa fa-lock"></i> </span>
            </div>
            <input
                id="password"
                name="password"
                class="form-control"
                :placeholder="$t('passwordLabel')"
                :aria-label="$t('passwordLabel')"
                type="password"
                v-model="form.password"
                required
            >
          </div> <!-- form-group// -->
          <div class="form-group input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"> <i class="fa fa-lock"></i> </span>
            </div>
            <input
                class="form-control"
                :placeholder="$t('passwordRepeatLabel')"
                id="password_repeat"
                name="password_repeat"
                :aria-label="$t('passwordRepeatLabel')"
                type="password"
                required
                v-model="form.passwordRepeat"
            >
          </div> <!-- form-group// -->
          <div class="input-group mb-3">
            <div class="form-check">
              <input
                  type="checkbox"
                  class="form-check-input"
                  id="terms_and_conditions"
                  required
                  v-model="form.termsAndConditions"
              >
              <label class="form-check-label" for="terms_and_conditions">{{ $t('termsConditionsFirstPart') }}
                <a
                    href="/index.php/tnc"
                    target="_blank"
                >{{ $t('termsAndConditions') }}</a></label>
            </div>
          </div>
          <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block" :disabled="this.registerButtonClicked === true">
              {{ $t('submit') }}
            </button>
          </div> <!-- form-group// -->
          <p class="text-center">{{ $t('backToLoginQuestion') }}<a href="/index.php/login"
                                                                   target="_blank"
          >{{ $t('backToLogin') }}</a></p>
        </form>
      </div>
    </div>

  </div>
</template>

<script>
import {AXIOS, StartUp} from "../../../../lib/js/src/StartUp";
import {Container} from "../../../../lib/js/src/DI/Container";
import {ROUTES} from "./config/routes";

export default {
  name: "App",
  created() {
    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();
    this.container.container = startUp.getContainer();
    this.container.axios = this.container.container.query(AXIOS);
  },
  data() {
    return {
      container: {
        container: null,
        axios: null
      },
      registerButtonClicked: false,
      form: {
        firstName: '',
        lastName: '',
        userName: '',
        password: '',
        phone: {
          prefix: '',
          value: ''
        },
        email: '',
        website: '',
        passwordRepeat: '',
        termsAndConditions: false
      }
    }
  },
  methods: {
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
            , 'phone': this.form.phone.prefix + this.form.phone.value
            , 'website': this.form.website
            , 'password': this.form.password
            , 'password_repeat': this.form.passwordRepeat
            , 'terms_and_conditions': this.form.termsAndConditions
          }
      )
          .then((data) => {

            // _this.longModal.show("Register", "OK", "OK", data['message']);
            alert(
                JSON.stringify(data)
            )
            this.registerButtonClicked = false;
            console.log(data);
          })
          .catch((data) => {
            alert(
                JSON.stringify(data)
            )
            // _this.longModal.show("Register", "OK", "OK", "There was an error during the registration. Please try it again or reach us out!");
            this.registerButtonClicked = false;
            console.log(data);
          })
    }
  }
}
</script>

<style scoped>

</style>