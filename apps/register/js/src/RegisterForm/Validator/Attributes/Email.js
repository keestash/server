import $ from "jquery";
import {Email as EmailValidator} from "../../../../../../../lib/js/src/Validation/Email";

export const EVENT_NAME_REGISTER_EMAIL = "email.register.name.event";

export class Email {
    validate() {
        const element = $("#tl__register__email");
        let emailValidator = new EmailValidator();

        element.keyup(() => {
            let value = element.val();
            let validEmail = emailValidator.isValidAddress(value) && value !== "";

            console.log(validEmail)

            $(document).trigger(
                EVENT_NAME_REGISTER_EMAIL
                , {
                    "valid": validEmail
                    , "hint_id": "#tl__register__email__invalid__text"
                }
            )

        });

    }
}
