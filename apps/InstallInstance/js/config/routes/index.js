import {Host} from "../../../../../lib/js/src/Backend/Host";

const UPDATE_CONFIG = "/install_instance/update_config/";
const CONFIG_DATA = "/install_instance/config_data/";
const END_UPDATE = "/install_instance/end_update/";

const host = new Host();

export const ROUTES = {
    INSTALL_INSTANCE_UPDATE_CONFIG: () => {
        return host.getApiHost() + UPDATE_CONFIG;
    },
    GET_INSTALL_INSTANCE_END_UPDATE: () => {
        return host.getApiHost() + END_UPDATE;
    },
    GET_INSTALL_INSTANCE_CONFIG_DATA: () => {
        return host.getApiHost() + CONFIG_DATA;
    },
};