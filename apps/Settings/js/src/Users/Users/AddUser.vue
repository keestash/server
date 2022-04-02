<template>
  <div v-if="show" @usersLoaded="handleUsersLoaded">
    <div>
      <button href="#reject" role="button" @click="toggle('add-user-modal')" type="button" class="btn btn-primary">
        {{ form.buttonText }}
      </button>

      <div :class="modalClasses" class="fade" id="reject" role="dialog" ref="add-user-modal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">{{ form.title }}</h4>
              <button type="button" class="close" @click="toggle()">&times;</button>
            </div>
            <div class="modal-body">
              <form
                  ref="form"
              >
                <span>{{ form.description }}</span>
                <div>
                  <div class="alert alert-danger" role="alert" v-if="form.success === 1">
                    {{ this.form.errorText }}
                  </div>
                  <div class="alert alert-success" role="alert" v-if="form.success === 2">
                    {{ this.form.successText }}
                  </div>
                </div>

                <div class="form-group">
                  <label for="user-name">{{ form.username.label }}</label>
                  <input
                      type="text"
                      class="form-control"
                      id="user-name"
                      :placeholder=form.username.placeholder
                      v-model="form.username.value"
                      required
                      aria-describedby="input-live-help input-live-feedback"
                      v-on:input="validateUserName"
                  >
                </div>

                <div class="form-group">
                  <label for="first-name">{{ form.firstname.label }}</label>
                  <input
                      type="text"
                      class="form-control"
                      id="first-name"
                      :placeholder=form.firstname.placeholder
                      v-model="form.firstname.value"
                      required
                      aria-describedby="input-live-help input-live-feedback"
                      v-on:input="validateFirstName"
                  >
                </div>

                <div class="form-group">
                  <label for="last-name">{{ form.lastname.label }}</label>
                  <input
                      type="text"
                      class="form-control"
                      id="last-name"
                      :placeholder=form.lastname.placeholder
                      v-model="form.lastname.value"
                      required
                      aria-describedby="input-live-help input-live-feedback"
                      v-on:input="validateLastName"
                  >
                </div>

                <div class="form-group">
                  <label for="email">{{ form.email.label }}</label>
                  <input
                      type="email"
                      class="form-control"
                      id="email"
                      :placeholder=form.email.placeholder
                      v-model="form.email.value"
                      required
                      aria-describedby="input-live-help input-live-feedback"
                      v-on:input="validateEmail"
                  >
                </div>

                <div class="form-group">
                  <label for="phone">{{ form.phone.label }}</label>
                  <input
                      type="text"
                      class="form-control"
                      id="phone"
                      :placeholder=form.phone.placeholder
                      v-model="form.phone.value"
                      required
                      aria-describedby="input-live-help input-live-feedback"
                      v-on:input="validatePhone"
                  >
                </div>

                <div class="form-group">
                  <label for="website">{{ form.website.label }}</label>
                  <input
                      type="text"
                      class="form-control"
                      id="website"
                      :placeholder=form.website.placeholder
                      v-model="form.website.value"
                      required
                      aria-describedby="input-live-help input-live-feedback"
                      v-on:input="validateWebsite"
                  >
                </div>

                <div class="form-group">
                  <label for="password">{{ form.password.label }}</label>
                  <input
                      type="password"
                      class="form-control"
                      id="password"
                      :placeholder=form.password.placeholder
                      v-model="form.password.value"
                      required
                      aria-describedby="input-live-help input-live-feedback"
                      v-on:input="validatePassword"
                  >
                </div>

                <div class="form-check">
                  <input type="checkbox" class="form-check-input" id="checkbox-1" v-model="form.locked.value"
                         name="checkbox-1" value="true">
                  <label class="form-check-label" for="checkbox-1">{{ form.locked.label }}</label>
                </div>

              </form>
              <button type="button" class="btn mt-3 btn-outline-primary" @click="onSubmit" data-type="add">
                <div class="spinner-border small" role="status" v-if="form.submitted">
                  <span class="sr-only"></span>
                </div>
                {{ this.form.buttonText }}
              </button>

              <button type="button" class="mt-2 btn btn-outline-secondary btn-block" @click="hideModal"
                      data-type="cancel">
                {{ this.form.negativeButtonText }}
              </button>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" @click="toggle()">Close</button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script>


import {ROUTES} from "../../config/routes";
import {AXIOS, EMAIL_VALIDATOR, PHONE_VALIDATOR, StartUp, URL_VALIDATOR} from "../../../../../../lib/js/src/StartUp";
import {RESPONSE_CODE_OK} from "../../../../../../lib/js/src/Backend/Axios";
import {Container} from "../../../../../../lib/js/src/DI/Container";

export default {

  name: "AddUser",
  components: {},
  props: [
    "show"
  ],
  methods: {
    toggle(ref) {
      document.body.className += ' modal-open'
      let modalClasses = this.modalClasses

      if (modalClasses.indexOf('d-block') > -1) {
        modalClasses.pop()
        modalClasses.pop()

        //hide backdrop
        let backdrop = document.querySelector('.modal-backdrop')
        document.body.removeChild(backdrop)
      } else {
        modalClasses.push('d-block')
        modalClasses.push('show')

        //show backdrop
        let backdrop = document.createElement('div')
        backdrop.classList = "modal-backdrop fade show";
        document.body.appendChild(backdrop)
      }
    },
    hideModal() {
      this.$refs['add-user-modal'].hide()
    },
    onSubmit() {
      this.form.submitted = true;
      this.validateUserName();
      this.validateFirstName();
      this.validateLastName();
      this.validateEmail();
      this.validatePhone();
      this.validatePassword();
      this.validateWebsite();

      let valid = true;
      const startUp = new StartUp(
          new Container()
      );
      startUp.setUp();
      const diContainer = startUp.getContainer();

      const axios = diContainer.query(AXIOS);

      for (let key in this.form) {
        if (!this.form.hasOwnProperty(key)) {
          continue;
        }

        if (this.form[key].state === false) {
          valid = false
          break
        }
      }

      if (false === valid) {
        this.form.submitted = false;
        return;
      }

      const data = {
        first_name: this.form.firstname.value
        , last_name: this.form.lastname.value
        , user_name: this.form.username.value
        , email: this.form.email.value
        , phone: this.form.phone.value
        , website: this.form.website.value
        , password: this.form.password.value
        , password_repeat: this.form.password.value
        , terms_and_conditions: true
        , locked: this.form.locked.value
      };

      axios.post(
          ROUTES.REGISTER_ADD()
          , data
      )
          .then(
              (response) => {
                this.form.submitted = false;
                return response.data
              }
          )
          .then(
              (data) => {
                this.form.success = (RESPONSE_CODE_OK in data) ? 2 : 1;

                if (this.form.success) {
                  for (let key in this.form) {
                    if (this.form.hasOwnProperty(key)) {
                      this.form[key].value = '';
                    }
                  }
                }
              }
          )
          .catch(
              () => {
                this.form.submitted = false;
                this.form.success = 1;
              }
          );
    },
    validateUserName() {
      const startUp = new StartUp(
          new Container()
      );
      startUp.setUp();
      const diContainer = startUp.getContainer();

      const axios = diContainer.query(AXIOS);

      if (this.form.username.value === "") {
        this.form.username.state = false;
        return;
      }
      axios.request(
          ROUTES.USER_EXISTS(this.form.username.value)
      ).then((response) => {
        const data = (response.request.response);

        if (RESPONSE_CODE_OK in data) {
          const _userExists = data[RESPONSE_CODE_OK]["messages"]["user_exists"];
          this.form.username.state = !(_userExists === 'true' || _userExists === true);
          return;
        }
        this.form.username.state = false;

      })
          .catch((response) => {
            console.log(response)
          })
      ;
    },
    handleUsersLoaded(loaded) {
      this.show = loaded;
    },
    validateFirstName() {
      this.form.firstname.state = this.form.firstname.value.length > 0;
    },
    validateLastName() {
      this.form.lastname.state = this.form.lastname.value.length > 0;
    },
    validateEmail() {
      const startUp = new StartUp(
          new Container()
      );
      startUp.setUp();
      const diContainer = startUp.getContainer();

      const emailValidator = diContainer.query(EMAIL_VALIDATOR);
      const axios = diContainer.query(AXIOS);
      const validEmail = this.form.email.value !== "" && emailValidator.isValidAddress(this.form.email.value);

      if (false === validEmail) {
        this.form.email.state = false;
        return;
      }

      axios.request(
          ROUTES.MAIL_EXISTS(this.form.email.value)
      ).then((response) => {
        const data = (response.request.response);

        if (RESPONSE_CODE_OK in data) {
          const _userExists = data[RESPONSE_CODE_OK]["messages"]["email_address_exists"];
          this.form.email.state = !(_userExists === 'true' || _userExists === true);
          return;
        }
        this.form.email.state = false;

      })
          .catch((response) => {
            console.log(response)
          })
      ;

    },
    validatePhone() {
      const startUp = new StartUp(
          new Container()
      );
      startUp.setUp();
      const diContainer = startUp.getContainer();
      const emailValidator = diContainer.query(PHONE_VALIDATOR);
      this.form.phone.state = this.form.phone.value.length === 0 || emailValidator.isValidNumber(this.form.phone.value);
    },
    validatePassword() {
      const startUp = new StartUp(
          new Container()
      );
      startUp.setUp();
      const diContainer = startUp.getContainer();
      const axios = diContainer.query(AXIOS);
      const password = this.form.password.value;

      if (password.length < 8) {
        this.form.password.state = false;
        return;
      }
      axios.request(
          ROUTES.PASSWORD_REQUIREMENTS()
          , {
            password: this.form.password.value
          }
      )
          .then((response) => {
            const data = (response.request.response);

            if (RESPONSE_CODE_OK in data) {
              this.form.password.state = true;
              return;
            }
            this.form.password.state = false;
          })
          .catch((x) => {
            console.log(x)
          });
    },
    validateWebsite() {
      const startUp = new StartUp(
          new Container()
      );
      startUp.setUp();
      const diContainer = startUp.getContainer();
      const urlValidator = diContainer.query(URL_VALIDATOR);
      this.form.website.state = this.form.website.value === "" || urlValidator.isValidURL(this.form.website.value);
    }
  },

  created() {

    // stringLoader.load(true)
    //     .then(() => {
    //       stringLoader.read()
    //           .then((strings) => {
    //             return ((strings["users"])["strings"])
    //           })
    //           .then((strings) => {
    //
    //             this.form.buttonText = strings['newUser']["positiveButton"];
    //             this.form.negativeButtonText = strings["newUser"]['negativeButton'];
    //             this.form.title = strings["newUser"]['title'];
    //             this.form.description = strings["newUser"]['description'];
    //
    //             this.form.username.label = strings["newUser"]["form"]["userName"]["label"];
    //             this.form.username.placeholder = strings["newUser"]["form"]["userName"]["placeholder"];
    //             this.form.username.invalidFeedback = strings["newUser"]["form"]["userName"]["invalidFeedback"];
    //
    //             this.form.firstname.label = strings["newUser"]["form"]["firstName"]["label"];
    //             this.form.firstname.placeholder = strings["newUser"]["form"]["firstName"]["placeholder"];
    //             this.form.firstname.invalidFeedback = strings["newUser"]["form"]["firstName"]["invalidFeedback"];
    //
    //             this.form.lastname.label = strings["newUser"]["form"]["lastName"]["label"];
    //             this.form.lastname.placeholder = strings["newUser"]["form"]["lastName"]["placeholder"];
    //             this.form.lastname.invalidFeedback = strings["newUser"]["form"]["lastName"]["invalidFeedback"];
    //
    //             this.form.email.label = strings["newUser"]["form"]["email"]["label"];
    //             this.form.email.placeholder = strings["newUser"]["form"]["email"]["placeholder"];
    //             this.form.email.invalidFeedback = strings["newUser"]["form"]["email"]["invalidFeedback"];
    //
    //             this.form.phone.label = strings["newUser"]["form"]["phone"]["label"];
    //             this.form.phone.placeholder = strings["newUser"]["form"]["phone"]["placeholder"];
    //             this.form.phone.invalidFeedback = strings["newUser"]["form"]["phone"]["invalidFeedback"];
    //
    //             this.form.website.label = strings["newUser"]["form"]["website"]["label"];
    //             this.form.website.placeholder = strings["newUser"]["form"]["website"]["placeholder"];
    //             this.form.website.invalidFeedback = strings["newUser"]["form"]["website"]["invalidFeedback"];
    //
    //             this.form.password.label = strings["newUser"]["form"]["password"]["label"];
    //             this.form.password.placeholder = strings["newUser"]["form"]["password"]["placeholder"];
    //             this.form.password.invalidFeedback = strings["newUser"]["form"]["password"]["invalidFeedback"];
    //
    //             this.form.locked.label = strings["newUser"]["form"]["locked"]["label"];
    //             this.form.successText = strings["newUser"]["successText"];
    //             this.form.errorText = strings["newUser"]["errorText"];
    //           })
    //       ;
    //     });

  },
  data() {
    return {
      modalClasses: ['modal', 'fade'],
      form: {
        username: {
          label: '',
          placeholder: '',
          value: '',
          invalidFeedback: '',
          state: null
        },
        firstname: {
          label: '',
          placeholder: '',
          value: '',
          invalidFeedback: '',
          state: null
        },
        lastname: {
          label: '',
          placeholder: '',
          value: '',
          invalidFeedback: '',
          state: null
        },
        email: {
          label: '',
          placeholder: '',
          value: '',
          invalidFeedback: '',
          state: null
        },
        phone: {
          label: '',
          placeholder: '',
          value: '',
          invalidFeedback: '',
          state: null
        },
        website: {
          label: '',
          placeholder: '',
          value: '',
          invalidFeedback: '',
          state: null
        },
        password: {
          label: '',
          placeholder: '',
          value: '',
          invalidFeedback: '',
          state: null
        },
        locked: {
          label: '',
          value: 'false'
        },
        title: '',
        description: '',
        buttonText: '',
        negativeButtonText: '',
        submitted: false,
        success: 0
      },
      successText: '',
      errorText: ''
    }
  }
}

</script>

<style scoped>

</style>
