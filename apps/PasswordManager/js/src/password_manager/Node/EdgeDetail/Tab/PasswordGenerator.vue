<template>
  <div>
    <div class="tab-pane" id="pwm__pw__generator" role="tabpanel">
      <div class="container mt-3 ">
        <div class="row">

          <div class="col">
            <div class="input-group">
              <input :placeholder="$t('credential.detail.passwordGeneratorPlaceholder')"
                     id="pwm__generator__input"
                     type="text"
                     class="form-control form-control-sm"
                     readonly
                     v-model="password"
              >
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col  mt-1">
            <div class="container">
              <div class="row justify-content-between">
                <div class="col-4">
                  <label>{{ $t('credential.detail.qualityLabel') }}:</label>
                  <span id="pwm__quality__label">{{ this.qualityValue }}</span>
                </div>
                <div class="col-4">
                  <label>{{ $t('credential.detail.entropyLabel') }}:</label>
                  <span id="pwm__entropy__value">{{ this.entropyValue }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <small>{{ $t('credential.detail.characterTypes') }}</small>

        <div class="border rounded">
          <div class="row">
            <div class="col">
              <div class="form-check">
                <input type="checkbox"
                       @change="onCheckbox($event,'upperCase')"
                >
                <label>{{ $t('credential.detail.upperCaseLabel') }}</label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="form-check">
                <input type="checkbox"
                       @change="onCheckbox($event,'lowerCase')"
                >
                <label>{{ $t('credential.detail.lowerCaseLabel') }}</label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="form-check">
                <input type="checkbox"
                       @change="onCheckbox($event,'digit')"
                >
                <label>{{ $t('credential.detail.digitLabel') }}</label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="form-check">
                <input type="checkbox"
                       @change="onCheckbox($event,'specialChar')"
                >
                <label>{{ $t('credential.detail.specialCharacterLabel') }}</label>
              </div>
            </div>
          </div>
        </div>
        <hr>
        <div class="row">
          <div class="col" id="pwm__range__input__wrapper">
            <input
                class="form-control-range"
                type="range"
                :min="this.minPasswordCharacters"
                :max="maxPasswordCharacters"
                :step="stepPasswordCharacters"
                v-model="characterCount"
                @change="onCheckbox($event,'value')"
            >
          </div>
        </div>
        <div class="row d-flex justify-content-between">
          <div class="col">
            <span>{{ minPasswordCharacters }}</span>
          </div>
          <div class="col">
            <span id="pwm__character__count">{{ characterCount }}</span>
          </div>
          <div class="col">
            <span>{{ maxPasswordCharacters }}</span>
          </div>
        </div>
        <div class="row">
          <div class="col">
            <button class="btn btn-primary" id="pwm__use__button">{{
                $t('credential.detail.usePassword')
              }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import _ from "lodash"
import {ROUTES} from "../../../../config/routes";
import {AXIOS, StartUp} from "../../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../../lib/js/src/DI/Container";
import {RESPONSE_CODE_OK, RESPONSE_FIELD_MESSAGES} from "../../../../../../../../lib/js/src/Backend/Axios";

export default {
  name: "PasswordGenerator",
  data() {
    return {
      characterCount: 0,
      password: null,
      qualityValue: "",
      entropyValue: 0,
      minPasswordCharacters: 8,
      maxPasswordCharacters: 50,
      stepPasswordCharacters: 1,
      defaultValue: 1,
      checkboxes: {
        upperCase: false,
        lowerCase: false,
        digit: false,
        specialChar: false,
        value: 8
      },
      doWork: () => {
      }
    }
  },
  watch: {
    characterCount: function (o, n) {
      if (o === n) return;
      if (n < this.minPasswordCharacters || n > this.maxPasswordCharacters) return;
      this.doWork();
    },
  },
  created() {
    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();

    this.container = startUp.getContainer();
    this.axios = this.container.query(AXIOS);

    this.defaultValue = this.maxPasswordCharacters / 2;
    this.characterCount = this.defaultValue;

    this.doWork = _.debounce(this.onChange, 900);
  },
  methods: {
    onCheckbox(e, type) {

      this.checkboxes = {
        value: (type === 'value') ? e.target.value : this.checkboxes.value,
        upperCase: (type === 'upperCase' && e.target.checked) || this.checkboxes.upperCase
        , lowerCase: (type === 'lowerCase' && e.target.checked) || this.checkboxes.lowerCase
        , digit: (type === 'digit' && e.target.checked) || this.checkboxes.digit
        , specialChar: (type === 'specialChar' && e.target.checked) || this.checkboxes.specialChar
      }

      this.doWork();

    },
    onChange() {

      this.axios.request(
          ROUTES.getGeneratePassword(
              this.checkboxes.value
              , this.checkboxes.upperCase
              , this.checkboxes.lowerCase
              , this.checkboxes.digit
              , this.checkboxes.specialChar
          )
      ).then(
          (response) => {
            if (RESPONSE_CODE_OK in response.data) {
              return response.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES];
            }
            return [];
          }
      )
          .then((data) => {
            this.password = data.response.password.value;
            this.entropyValue = data.response.password.entropy;
            this.qualityValue = data.response.strings.quality[data.response.password.quality];
          });

    }
  }
}
</script>

<style scoped>

</style>