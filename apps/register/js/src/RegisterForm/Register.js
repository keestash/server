import {Validator} from "./Validator/Validator";
import {FirstName} from "./Validator/Attributes/FirstName";
import {LastName} from "./Validator/Attributes/LastName";
import {UserName} from "./Validator/Attributes/UserName";
import {Email} from "./Validator/Attributes/Email";
import $ from "jquery";
import modal from "../../../../../lib/js/src/UI/modal";
import {Phone} from "./Validator/Attributes/Phone";
import {Website} from "./Validator/Attributes/Website";
import {Password} from "./Validator/Attributes/Password";
import {PasswordRepeat} from "./Validator/Attributes/PasswordRepeat";
import {TermsAndConditions} from "./Validator/Attributes/TermsAndConditions";
import {RESPONSE_CODE_NOT_OK, RESPONSE_CODE_OK} from "../../../../../lib/js/src/Backend/Request";

export class Register {

    constructor(
        request
        , routes
        , uiService
    ) {
        this.request = request;
        this.routes = routes;
        this.uiService = uiService;
        this.registerButtonClicked = false;
        this.registerButton = $("#tl__register__button");
    }

    setup() {
        const validator = new Validator(
            this.uiService
        );
        validator.register(new FirstName());
        validator.register(new LastName());
        validator.register(
            new UserName(
                this.request
                , this.routes
            )
        );
        validator.register(new Email());
        validator.register(new Phone());
        validator.register(new Website());
        validator.register(
            new Password(
                this.request
                , this.routes
            )
        );
        validator.register(new PasswordRepeat());
        validator.register(new TermsAndConditions());
        validator.validate();
    }

    setUpClickListener() {
        const _this = this;

        this.registerButton.click((event) => {
            event.preventDefault();

            if (true === _this.registerButtonClicked) return;
            _this.disableForm();

            let firstName = $("#tl__register__first__name").val();
            let lastName = $("#tl__register__last__name").val();
            let userName = $("#tl__register__user__name").val();
            let password = $("#tl__register__password").val();
            let email = $("#tl__register__email").val();
            let phone = $("#tl__register__phone").val();
            let website = $("#tl__register__website").val();
            let passwordRepeat = $("#tl__register__password__repeat").val();
            let termsAndConditions = $("#tl__register__terms__and__conditions").is(':checked');

            let values = {
                'first_name': firstName
                , 'last_name': lastName
                , 'user_name': userName
                , 'email': email
                , 'phone': phone
                , 'website': website
                , 'password': password
                , 'password_repeat': passwordRepeat
                , 'terms_and_conditions': termsAndConditions
            };

            _this.request.post(
                _this.routes.getRegisterAdd()
                , values
                , (response) => {
                    let object = JSON.parse(response);
                    let data = null;

                    if (RESPONSE_CODE_OK in object) {
                        data = object[RESPONSE_CODE_OK];
                    } else if (RESPONSE_CODE_NOT_OK in object) {
                        data = object[RESPONSE_CODE_NOT_OK];
                    }

                    modal.miniModal(data['message']);
                    _this.enableForm();
                }
                , () => {
                    modal.miniModal("There was an error during the registration. Please try it again or reach us out!");
                    _this.enableForm();
                }
            )

        });
    }

    enableForm() {
        this.registerButtonClicked = false;
        this.registerButton.removeClass("disabled");
    }

    disableForm() {
        this.registerButtonClicked = true;
        this.registerButton.addClass("disabled");
    }

}
