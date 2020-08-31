import $ from "jquery";
import {RESPONSE_CODE_OK} from "../../../../../../../lib/js/src/Backend/Request";

export const EVENT_NAME_REGISTER_USER_NAME = "name.user.register.name.event";

export class UserName {

    constructor(
        request
        , routes
    ) {
        this.request = request;
        this.routes = routes;
    }

    validate() {
        const _this = this;
        const element = $("#tl__register__user__name");

        element.focusout(async () => {

            let value = element.val();
            value = value.trim();

            if ("" === value) {
                _this.notify(false);
            }

            _this.request.get(
                _this.routes.getUserExists(value)
                , {}
                , (response) => {
                    let object = JSON.parse(response);

                    let userExists = false;

                    if (RESPONSE_CODE_OK in object) {
                        const _userExists = object[RESPONSE_CODE_OK]["messages"]["user_exists"];
                        userExists = _userExists === 'true' || _userExists === true;
                    }

                    _this.notify(false === userExists);

                }
            )


        });

    }

    notify(valid) {
        $(document).trigger(
            EVENT_NAME_REGISTER_USER_NAME
            , {
                "valid": true === valid
                , "hint_id": "#tl__register__user__name_invalid__text"
            }
        );
    }
}
