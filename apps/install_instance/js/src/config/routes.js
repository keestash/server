import {Host} from "../../../../../lib/js/src/Backend/Host";

const UPDATE_CONFIG = "/install_instance/update_config/";

const host = new Host();

export const ROUTES = {
    INSTALL_INSTANCE_UPDATE_CONFIG: () => {
        return host.getApiHost() + UPDATE_CONFIG;
    }

};