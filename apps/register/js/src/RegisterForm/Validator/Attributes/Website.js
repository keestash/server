import $ from "jquery";
import {Website as WebsiteValidator} from "../../../../../../../lib/js/src/Validation/Website";

export const EVENT_NAME_REGISTER_WEBSITE = "website.register.name.event";

export class Website {
    validate() {
        const element = $("#tl__register__website");
        let websiteValidator = new WebsiteValidator();

        element.keyup(() => {
            let value = element.val();
            let validEmail = websiteValidator.isValidURL(value) || value === "";

            $(document).trigger(
                EVENT_NAME_REGISTER_WEBSITE
                , {
                    "valid": validEmail
                    , "hint_id": "#tl__register__website__invalid__text"
                }
            )

        });

    }
}
