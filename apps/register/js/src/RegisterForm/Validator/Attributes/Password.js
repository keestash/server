import $ from "jquery";
import {RESPONSE_CODE_NOT_OK, RESPONSE_CODE_OK} from "../../../../../../../lib/js/src/Backend/Request";

export const EVENT_NAME_REGISTER_PASSWORD = "password.register.name.event";
const MINIMUM_NUMBER_OF_CHARACTERS_FOR_USER_PASSWORD = 8;

export class Password {

    constructor(
        request
        , routes
    ) {
        this.request = request;
        this.routes = routes;
    }

    validate() {
        const _this = this;
        const element = $("#tl__register__password");

        element.focusout(() => {
            let value = element.val();
            let valLength = value.length;

            if (valLength < MINIMUM_NUMBER_OF_CHARACTERS_FOR_USER_PASSWORD) {

                $(document).trigger(
                    EVENT_NAME_REGISTER_PASSWORD
                    , {
                        "valid": false
                        , "hint_id": "#tl__register__password__invalid__text"
                    }
                )

                return;

            }

            _this.request.post(
                _this.routes.getPasswordRequirements()
                , {
                    'password': value
                }
                , (response) => {
                    let obj = JSON.parse(response);

                    let validPassword = false;

                    if (RESPONSE_CODE_OK in obj) {
                        validPassword = true;
                    } else if (RESPONSE_CODE_NOT_OK in obj) {
                        validPassword = false;
                    }

                    $(document).trigger(
                        EVENT_NAME_REGISTER_PASSWORD
                        , {
                            "valid": true === validPassword && value !== ""
                            , "hint_id": "#tl__register__password__invalid__text"
                        }
                    );

                }
            )

        });

    }
}
