import $ from "jquery";

export const EVENT_NAME_REGISTER_FIRST_NAME = "name.first.register.name.event";

export class FirstName {

    validate() {
        const element = $("#tl__register__first__name");

        element.keyup(() => {
            let value = element.val();
            value = value.trim();

            $(document).trigger(
                EVENT_NAME_REGISTER_FIRST_NAME
                , {
                    "valid": value !== ""
                    , "hint_id": "#tl__register__first__name_invalid__text"
                }
            );

        });

    }
}
