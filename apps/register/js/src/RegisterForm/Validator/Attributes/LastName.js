import $ from "jquery";

export const EVENT_NAME_REGISTER_LAST_NAME = "name.last.register.name.event";

export class LastName {

    validate() {
        const element = $("#tl__register__last__name");

        element.keyup(() => {
            let value = element.val();
            value = value.trim();

            $(document).trigger(
                EVENT_NAME_REGISTER_LAST_NAME
                , {
                    "valid": value !== ""
                    , "hint_id": "#tl__register__last__name_invalid__text"
                }
            );

        });

    }
}
