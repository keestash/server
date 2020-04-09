import {Host} from "../../../../../lib/js/src/Backend/Host";

const USERS_ADD_USER = "/users/add/";

export class Routes {
    constructor() {
        this.host = new Host();
    }

    getAddUser() {
        return this.host.getApiHost() + USERS_ADD_USER;
    }
}