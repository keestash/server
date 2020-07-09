import {Host} from "../../../../../lib/js/src/Backend/Host";

const FORGOT_PASSWORD_SUBMIT = "/forgot_password/submit";

export class Routes {
    constructor() {
        this.host = new Host();
    }

    getForgotPasswordSubmit() {
        return this.host.getApiHost() + FORGOT_PASSWORD_SUBMIT;
    }
}