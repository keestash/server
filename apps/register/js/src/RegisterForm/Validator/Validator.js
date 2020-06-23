import $ from "jquery";
import {EVENT_NAME_REGISTER_FIRST_NAME} from "./Attributes/FirstName";
import {EVENT_NAME_REGISTER_LAST_NAME} from "./Attributes/LastName";
import {EVENT_NAME_REGISTER_EMAIL} from "./Attributes/Email";
import {EVENT_NAME_REGISTER_PASSWORD} from "./Attributes/Password";
import {EVENT_NAME_REGISTER_PASSWORD_REPEAT} from "./Attributes/PasswordRepeat";
import {EVENT_NAME_REGISTER_PHONE} from "./Attributes/Phone";
import {EVENT_NAME_REGISTER_TERMS_AND_CONDITIONS} from "./Attributes/TermsAndConditions";
import {EVENT_NAME_REGISTER_USER_NAME} from "./Attributes/UserName";
import {EVENT_NAME_REGISTER_WEBSITE} from "./Attributes/Website";

const DEFAULT_THROTTLE_TIME = 500;

export class Validator {

    constructor(uiService) {
        this.uiService = uiService;

        this.validators = [];
        this.DATA_BAG = {
            FIRST_NAME: false
            , LAST_NAME: false
            , USER_NAME: false
            , PHONE: false
            , WEBSITE: false
            , PASSWORD: false
            , EMAIL: false
            , TERMS_AND_CONDITIONS: false
            , PASSWORD_REPEAT: false
        };
    }

    register(validator) {
        this.validators.push(validator)
    }

    validate() {

        for (let i = 0; i < this.validators.length; i++) {
            const validator = this.validators[i];
            validator.validate()
        }

        this.listenToEvents();

    }

    listenToEvents() {
        const _this = this;
        $(document).on(
            EVENT_NAME_REGISTER_EMAIL
            , (event, response) => {
                const valid = response.valid || false;
                const hintId = response.hint_id || null;
                _this.DATA_BAG.EMAIL = valid;
                _this.isEnabled(valid, hintId);
            });
        $(document).on(
            EVENT_NAME_REGISTER_FIRST_NAME
            , (event, response) => {
                const valid = response.valid || false;
                const hintId = response.hint_id || null;
                _this.DATA_BAG.FIRST_NAME = valid;
                _this.isEnabled(valid, hintId);
            });
        $(document).on(
            EVENT_NAME_REGISTER_LAST_NAME
            , (event, response) => {
                const valid = response.valid || false;
                const hintId = response.hint_id || null;
                _this.DATA_BAG.LAST_NAME = valid;
                _this.isEnabled(valid, hintId);
            });
        $(document).on(
            EVENT_NAME_REGISTER_PASSWORD
            , (event, response) => {
                const valid = response.valid || false;
                const hintId = response.hint_id || null;
                _this.DATA_BAG.PASSWORD = valid;
                _this.isEnabled(valid, hintId);
            });
        $(document).on(
            EVENT_NAME_REGISTER_PASSWORD_REPEAT
            , (event, response) => {
                const valid = response.valid || false;
                const hintId = response.hint_id || null;
                _this.DATA_BAG.PASSWORD_REPEAT = valid;
                _this.isEnabled(valid, hintId);
            });
        $(document).on(
            EVENT_NAME_REGISTER_PHONE
            , (event, response) => {
                const valid = response.valid || false;
                const hintId = response.hint_id || null;
                _this.DATA_BAG.PHONE = valid;
                _this.isEnabled(valid, hintId);
            });
        $(document).on(
            EVENT_NAME_REGISTER_TERMS_AND_CONDITIONS
            , (event, response) => {
                const valid = response.valid || false;
                const hintId = response.hint_id || null;
                _this.DATA_BAG.TERMS_AND_CONDITIONS = valid;
                _this.isEnabled(valid, hintId);
            });
        $(document).on(
            EVENT_NAME_REGISTER_USER_NAME
            , (event, response) => {
                const valid = response.valid || false;
                const hintId = response.hint_id || null;
                _this.DATA_BAG.USER_NAME = valid;
                _this.isEnabled(valid, hintId);
            });
        $(document).on(
            EVENT_NAME_REGISTER_WEBSITE
            , (event, response) => {
                const valid = response.valid || false;
                const hintId = response.hint_id || null;
                _this.DATA_BAG.WEBSITE = valid;
                _this.isEnabled(valid, hintId);
            });
    }

    isEnabled(valid, hintId) {
        const _this = this;

        _this.changeState(
            false === valid
            , hintId
        );

        _this.uiService.throttle(
            () => {
                let isValid = _this.validateForMinimumValues(false);
                let isValidWithToc = _this.validateForMinimumValues(true);

                console.log(_this.DATA_BAG);
                console.log(isValid);
                console.log(isValidWithToc);

                _this.changeTocState(true === isValid);
                _this.changeButtonState(true === isValidWithToc);

            }
            , DEFAULT_THROTTLE_TIME
        )()

    }

    validateForMinimumValues(includeToc = false) {
        let isValid =
            true === this.DATA_BAG.FIRST_NAME
            && true === this.DATA_BAG.LAST_NAME
            && true === this.DATA_BAG.USER_NAME
            && true === this.DATA_BAG.EMAIL
            && true === this.DATA_BAG.PASSWORD
            && true === this.DATA_BAG.PASSWORD_REPEAT
            && true === this.DATA_BAG.PHONE
            && true === this.DATA_BAG.WEBSITE;
        if (false === includeToc) return isValid;
        return true === isValid && true === this.DATA_BAG.TERMS_AND_CONDITIONS;
    }

    changeState(show, selectorName) {
        let element = $(selectorName);

        if (null === element || typeof element === 'undefined') return;

        if (true === show) {

            element.fadeIn(500, function () {
                $(this).show();
            });

        } else {

            element.fadeOut(500, function () {
                $(this).hide();
            });

        }
    }

    changeTocState(enable) {
        let toc = $("#tl__register__terms__and__conditions");
        if (true === enable) {
            toc.removeAttr("disabled");
            return;
        }

        toc.attr("disabled", true);
    }

    changeButtonState(enable) {
        let button = $("#tl__register__button");

        button.addClass("disabled");
        if (true === enable) {
            button.removeClass("disabled");
        }

    }
}
