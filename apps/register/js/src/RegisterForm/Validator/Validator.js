import System from "../../../../../../lib/js/src/System";
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

export class Validator {

    constructor() {
        this.validators = [];
        this.DATA_BAG = {
            FIRST_NAME: false
            , LAST_NAME: false
            , USER_NAME: false
            , PASSWORD: false
            , EMAIL: false
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
                const valid = response.valid || false
                const hintId = response.hint_id || null
                _this.isEnabled(valid, hintId);
            });
        $(document).on(
            EVENT_NAME_REGISTER_FIRST_NAME
            , (event, response) => {
                const valid = response.valid || false
                const hintId = response.hint_id || null
                _this.isEnabled(valid, hintId);
            });
        $(document).on(
            EVENT_NAME_REGISTER_LAST_NAME
            , (event, response) => {
                const valid = response.valid || false
                const hintId = response.hint_id || null
                _this.isEnabled(valid, hintId);
            });
        $(document).on(
            EVENT_NAME_REGISTER_PASSWORD
            , (event, response) => {
                const valid = response.valid || false
                const hintId = response.hint_id || null
                _this.isEnabled(valid, hintId);
            });
        $(document).on(
            EVENT_NAME_REGISTER_PASSWORD_REPEAT
            , (event, response) => {
                const valid = response.valid || false
                const hintId = response.hint_id || null
                _this.isEnabled(valid, hintId);
            });
        $(document).on(
            EVENT_NAME_REGISTER_PHONE
            , (event, response) => {
                const valid = response.valid || false
                const hintId = response.hint_id || null
                _this.isEnabled(valid, hintId);
            });
        $(document).on(
            EVENT_NAME_REGISTER_TERMS_AND_CONDITIONS
            , (event, response) => {
                const valid = response.valid || false
                const hintId = response.hint_id || null
                _this.isEnabled(valid, hintId);
            });
        $(document).on(
            EVENT_NAME_REGISTER_USER_NAME
            , (event, response) => {
                const valid = response.valid || false
                const hintId = response.hint_id || null
                _this.isEnabled(valid, hintId);
            });
        $(document).on(
            EVENT_NAME_REGISTER_WEBSITE
            , (event, response) => {
                const valid = response.valid || false
                const hintId = response.hint_id || null
                _this.isEnabled(valid, hintId);
            });
    }

    isEnabled(valid, hintId) {
        const system = new System();
        const _this = this;

        // show hint
        _this.changeState(
            false === valid
            , hintId
        );

        return;
        system.throttle(
            () => {
                let enabled = true;
                let element = $("#tl__register__terms__and__conditions");

                $.each(thiz.DATA_BAG, (i, v) => {
                    enabled = v && enabled;
                });

                if (true === enabled) {
                    element.removeAttr("disabled");
                    return;
                }
                element.prop("checked", false);
                element.removeAttr("checked");
                element.attr("disabled", true);
                thiz.changeButtonState(false)
            }, 500)()
    }

    changeState(show, selectorName) {
        let element = $(selectorName);

        // console.log(element);
        // console.log(selectorName);
        // console.log(show);
        if (null === element || typeof element === 'undefined') return;


        if (true === show) {
            // console.log("showing element 12345");
            // console.log(selectorName);
            // console.log(element);
            element.fadeIn(500, function () {
                $(this).show();
            });
        } else {
            element.fadeOut(500, function () {
                $(this).hide();
            });
        }
    }

    changeButtonState(enable) {
        let button = $("#tl__register__button");

        button.addClass("disabled");
        if (true === enable) {
            button.removeClass("disabled");
        }

    }
}
