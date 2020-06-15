import $ from "jquery";

export const EVENT_NAME_REGISTER_USER_NAME = "name.user.register.name.event";

export class UserName {

    validate() {
        const element = $("#tl__register__user__name");

        element.keyup(() => {
            let value = element.val();
            $(document).trigger(
                EVENT_NAME_REGISTER_USER_NAME
                , {
                    "valid": value !== ""
                    , "hint_id": "#tl__register__user__name_invalid__text"
                }
            )
        });

    }
}
