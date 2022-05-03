<template>
  <div>
    <div class="tab-pane" id="pwm__pw__generator" role="tabpanel">
      <div class="container mt-3 ">
        <div class="row">
          <div class="col p-0">
            <div class="input-group">
              <div class="container">
                <template v-if="this.state.value === this.state.states.STATE_LOADED">
                  <input :placeholder="$t('credential.detail.passwordGeneratorPlaceholder')"
                         id="pwm__generator__input"
                         type="text"
                         class="form-control form-control-sm"
                         readonly
                         v-model="password"
                  >
                </template>
                <Skeleton height="25px" v-else/>
              </div>

            </div>
          </div>
        </div>
        <div class="row">
          <div class="col mt-1 p-0">
            <div class="container">
              <div class="row justify-content-between">
                <div class="col-4">
                  <small>{{ $t('credential.detail.qualityLabel') }}:</small>
                  <template v-if="this.state.value === this.state.states.STATE_LOADED">
                    <small>{{ this.qualityValue }}</small>
                  </template>
                  <Skeleton height="25px" width="75%" v-else/>
                </div>
                <div class="col-4">
                  <small>{{ $t('credential.detail.entropyLabel') }}:</small>
                  <template v-if="this.state.value === this.state.states.STATE_LOADED">
                    <small>{{ entropyFormatted }}</small>
                  </template>
                  <Skeleton height="25px" width="63%" v-else/>
                </div>
              </div>
            </div>
          </div>
        </div>
        <small>{{ $t('credential.detail.characterTypes') }}</small>

        <div class="border rounded">
          <div class="row">
            <div class="col mt-2">
              <div class="form-check">

                <input type="checkbox"
                       @change="onCheckbox($event,'upperCase')"
                       :disabled="isLoading"
                >
                <label>{{ $t('credential.detail.upperCaseLabel') }}</label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col mt-2">
              <div class="form-check">
                <input type="checkbox"
                       @change="onCheckbox($event,'lowerCase')"
                       :disabled="isLoading"
                >
                <label>{{ $t('credential.detail.lowerCaseLabel') }}</label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col mt-2">
              <div class="form-check">
                <input type="checkbox"
                       @change="onCheckbox($event,'digit')"
                       :disabled="isLoading"
                >
                <label>{{ $t('credential.detail.digitLabel') }}</label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col mt-2">
              <div class="form-check">
                <input type="checkbox"
                       @change="onCheckbox($event,'specialChar')"
                       :disabled="isLoading"
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
                :disabled="isLoading"
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
            <button type="button" class="btn btn-block btn-primary"
                    @click="onButtonClick"
            >{{
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
import {ROUTES} from "../../../../../config/routes/index";
import {AXIOS, StartUp} from "../../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../../lib/js/src/DI/Container";
import {RESPONSE_CODE_OK, RESPONSE_FIELD_MESSAGES} from "../../../../../../../../lib/js/src/Backend/Axios";
import {Skeleton} from 'vue-loading-skeleton';

const STATE_LOADING = 2;
const STATE_LOADED = 3;

export default {
  name: "PasswordGenerator",
  components: {Skeleton},
  data() {
    return {
      characterCount: 0,
      password: null,
      qualityValue: "",
      entropyValue: "",
      minPasswordCharacters: 8,
      maxPasswordCharacters: 50,
      stepPasswordCharacters: 1,
      defaultValue: 1,
      state: {
        value: STATE_LOADED,
        states: {
          STATE_LOADED: STATE_LOADED
        }
      },
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
  computed: {
    entropyFormatted: function () {
      return Math.round(this.entropyValue * 100) / 100;
    },
    isLoading: function () {
      return this.state.value === this.state.states.STATE_LOADING;
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
    this.checkboxes.value = this.defaultValue;

    this.doWork = _.debounce(this.onChange, 900);
  },
  methods: {
    onButtonClick(e) {
      e.preventDefault();
      if (
          this.state.value === STATE_LOADING
          || this.password === null
      ) return;
      this.$emit('passwordUsed', this.password);
    },
    onCheckbox(e, type) {

      this.state.value = STATE_LOADING;
      this.checkboxes = {
        value: (type === 'value') ? e.target.value : this.checkboxes.value,
        upperCase: (type === 'upperCase') ? e.target.checked : this.checkboxes.upperCase
        , lowerCase: (type === 'lowerCase') ? e.target.checked : this.checkboxes.lowerCase
        , digit: (type === 'digit') ? e.target.checked : this.checkboxes.digit
        , specialChar: (type === 'specialChar') ? e.target.checked : this.checkboxes.specialChar
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
            this.state.value = STATE_LOADED;
          });

    }
  }
}
</script>

<style scoped>

</style>