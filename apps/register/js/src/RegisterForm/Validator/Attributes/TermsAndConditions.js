import $ from "jquery";
import {EVENT_NAME_REGISTER_PASSWORD} from "./Password";

export const EVENT_NAME_REGISTER_TERMS_AND_CONDITIONS = "conditions.and.terms.register.name.event";

export class TermsAndConditions {
    validate() {
        const element = $("#tl__register__terms__and__conditions");
        element.change(() => {
            $(document).trigger(
                EVENT_NAME_REGISTER_TERMS_AND_CONDITIONS
                , {"valid": element.is(":checked")}
            )
        });

    }
}
