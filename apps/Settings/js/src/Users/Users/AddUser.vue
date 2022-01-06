<template>
  <div v-if="show" @usersLoaded="handleUsersLoaded">
    <div>
      <b-button
          v-b-modal.modal-1
      >{{ form.buttonText }}
      </b-button>

      <b-modal
          id="modal-1"
          ref="add-user-modal"
          centered scrollable hide-footer
          size="lg"
          :title=form.title
          @ok="onSubmit"
      >
        <b-form
            ref="form"
        >
          <span>{{ form.description }}</span>
          <div>
            <b-alert show v-if="form.success === 1" variant="danger">{{ this.form.errorText }}</b-alert>
            <b-alert show v-if="form.success === 2" variant="success">{{ this.form.successText }}</b-alert>
          </div>

          <b-form-group
              id="user-name"
              :label=form.username.label
              label-for="user-name"
              :invalid-feedback="form.username.invalidFeedback"
              :state="form.username.state"
          >
            <b-form-input
                id="user-name"
                v-model="form.username.value"
                type="text"
                required
                :placeholder=form.username.placeholder
                aria-describedby="input-live-help input-live-feedback"
                v-on:input="validateUserName"
                :state="form.username.state"
                trim
            ></b-form-input>

          </b-form-group>
          <b-form-group
              id="first-name"
              :label=form.firstname.label
              label-for="first-name"
              :invalid-feedback="form.firstname.invalidFeedback"
              :state="form.firstname.state"

          >
            <b-form-input
                id="first-name"
                v-model="form.firstname.value"
                required
                :placeholder=form.firstname.placeholder
                aria-describedby="input-live-help input-live-feedback"
                v-on:input="validateFirstName"
                :state="form.firstname.state"
                trim
            ></b-form-input>
          </b-form-group>

          <b-form-group
              id="last-name"
              :label=form.lastname.label
              label-for="last-name"
              :invalid-feedback="form.lastname.invalidFeedback"
              :state="form.lastname.state"
          >
            <b-form-input
                id="last-name"
                v-model="form.lastname.value"
                required
                :placeholder=form.lastname.placeholder
                aria-describedby="input-live-help input-live-feedback"
                v-on:input="validateLastName"
                :state="form.lastname.state"
                trim
            ></b-form-input>
          </b-form-group>

          <b-form-group
              id="email"
              :label=form.email.label
              label-for="email"
              :invalid-feedback="form.email.invalidFeedback"
              :state="form.email.state"
          >
            <b-form-input
                id="email"
                v-model="form.email.value"
                type="email"
                required
                :placeholder=form.email.placeholder
                aria-describedby="input-live-help input-live-feedback"
                v-on:input="validateEmail"
                :state="form.email.state"
                trim
            ></b-form-input>
          </b-form-group>

          <b-form-group
              id="phone"
              :label=form.phone.label
              label-for="phone"
              :invalid-feedback="form.phone.invalidFeedback"
              :state="form.phone.state"

          >
            <b-form-input
                id="phone"
                v-model="form.phone.value"
                type="text"
                :placeholder=form.phone.placeholder
                aria-describedby="input-live-help input-live-feedback"
                v-on:input="validatePhone"
                :state="form.phone.state"
                trim
            ></b-form-input>
          </b-form-group>

          <b-form-group
              id="website"
              :label=form.website.label
              label-for="website"
              :invalid-feedback="form.website.invalidFeedback"
              :state="form.website.state"

          >
            <b-form-input
                id="website"
                v-model="form.website.value"
                type="text"
                :placeholder=form.website.placeholder
                aria-describedby="input-live-help input-live-feedback"
                v-on:input="validateWebsite"
                :state="form.website.state"
                trim
            ></b-form-input>
          </b-form-group>

          <b-form-group
              id="password"
              :label=form.password.label
              label-for="password"
              :invalid-feedback="form.password.invalidFeedback"
              :state="form.password.state"

          >
            <b-form-input
                id="password"
                v-model="form.password.value"
                type="text"
                :placeholder=form.password.placeholder
                aria-describedby="input-live-help input-live-feedback"
                v-on:input="validatePassword"
                :state="form.password.state"
                trim
            ></b-form-input>
          </b-form-group>

          <b-form-group>
            <b-form-checkbox
                id="checkbox-1"
                v-model="form.locked.value"
                name="checkbox-1"
                value="true"
                unchecked-value="false"
            >
              {{ form.locked.label }}
            </b-form-checkbox>
          </b-form-group>


        </b-form>

        <b-button class="mt-3" variant="outline-primary" block @click="onSubmit" data-type="add">
          <b-spinner small v-if="form.submitted"></b-spinner>
          {{ this.form.buttonText }}
        </b-button>
        <b-button class="mt-2" variant="outline-secondary" block @click="hideModal" data-type="cancel">
          {{ this.form.negativeButtonText }}
        </b-button>

      </b-modal>
    </div>
  </div>
</template>

<script>


import {ROUTES} from "../../config/routes";
import {
  AXIOS,
  EMAIL_VALIDATOR,
  PHONE_VALIDATOR,
  URL_VALIDATOR
} from "../../../../../../lib/js/src/StartUp";
import {RESPONSE_CODE_OK} from "../../../../../../lib/js/src/Backend/Axios";

export default {

  name: "AddUser",
  components: {},
  props: [
    "show"
  ],
  methods: {
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
      const diContainer = Keestash.Main.getContainer();
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
      const diContainer = Keestash.Main.getContainer();
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
      const diContainer = Keestash.Main.getContainer();
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
      const diContainer = Keestash.Main.getContainer();
      const emailValidator = diContainer.query(PHONE_VALIDATOR);
      this.form.phone.state = this.form.phone.value.length === 0 || emailValidator.isValidNumber(this.form.phone.value);
    },
    validatePassword() {
      const diContainer = Keestash.Main.getContainer();
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
      const diContainer = Keestash.Main.getContainer();
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
