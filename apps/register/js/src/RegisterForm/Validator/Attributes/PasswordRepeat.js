import $ from "jquery";

export const EVENT_NAME_REGISTER_PASSWORD_REPEAT = "repeat.password.register.name.event";

export class PasswordRepeat {
    validate() {
        const element = $("#tl__register__password__repeat");
        const passwordElement = $("#tl__register__password");

        element.keyup(
            () => {
                let value = element.val();
                let passwordValue = passwordElement.val();
                passwordValue = passwordValue.trim();
                const isValid = value === passwordValue && value !== "";
                console.log(isValid);

                $(document).trigger(
                    EVENT_NAME_REGISTER_PASSWORD_REPEAT
                    , {
                        "valid": isValid
                        , "hint_element_id": "#tl__register__password__repeat__invalid__text"
                    }
                )

            });
    }
}
