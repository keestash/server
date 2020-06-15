import $ from "jquery";
import {Phone as PhoneValidator} from "../../../../../../../lib/js/src/Validation/Phone";

export const EVENT_NAME_REGISTER_PHONE = "phone.register.name.event";

export class Phone {
    validate() {
        const element = $("#tl__register__phone");
        let phoneValidator = new PhoneValidator();

        element.keyup(() => {
            let value = element.val();
            let validEmail = phoneValidator.isValidNumber(value) || value === "";

            $(document).trigger(
                EVENT_NAME_REGISTER_PHONE
                , {
                    "valid": validEmail
                    , "hint_id": "#tl__register__phone__invalid__text"
                }
            )

        });

    }
}
