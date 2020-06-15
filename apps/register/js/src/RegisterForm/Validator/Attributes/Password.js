import $ from "jquery";
import {RESPONSE_CODE_NOT_OK, RESPONSE_CODE_OK} from "../../../../../../../lib/js/src/UI/ModalHandler";

export const EVENT_NAME_REGISTER_PASSWORD = "password.register.name.event";

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

        // console.log(_this.routes)
        console.log(_this.request)

        element.keyup(() => {
            let value = element.val();
            let valLength = value.length;

            if (valLength < 8) {
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

                    let validPazzword = false;

                    if (RESPONSE_CODE_OK in obj) {
                        validPazzword = true;
                    } else if (RESPONSE_CODE_NOT_OK in obj) {
                        validPazzword = false;
                    }

                    $(document).trigger(
                        EVENT_NAME_REGISTER_PASSWORD
                        , {
                            "valid": false === validPazzword && value !== ""
                            , "hint_id": "#tl__register__password__invalid__text"
                        }
                    )
                }
            )

        });

    }
}
