import $ from "jquery";
import {EVENT_NAME_REGISTER_PASSWORD} from "./Password";

export const EVENT_NAME_REGISTER_PASSWORD_REPEAT = "repeat.password.register.name.event";

export class PasswordRepeat {
    validate() {
        const element = $("#tl__register__password__repeat");
        const passwordElement = $("#tl__register__password");

        element.keyup(function () {
            let value = element.val();
            let passwordValue = passwordElement.val();

            $(document).trigger(
                EVENT_NAME_REGISTER_PASSWORD_REPEAT
                , {
                    "valid": value !== passwordValue && value !== ""
                    , "hint_element_id": "#tl__register__password__repeat__invalid__text"
                }
            )

        });
    }
}
