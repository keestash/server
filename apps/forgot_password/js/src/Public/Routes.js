import {Host} from "../../../../../lib/js/src/Backend/Host";

const RESET_PASSWORD = "/reset_password/update/";

export class Routes {
    constructor() {
        this.host = new Host();
    }

    getResetPasswordSubmit() {
        return this.host.getApiHost() + RESET_PASSWORD;
    }
}