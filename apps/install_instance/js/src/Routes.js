export const UPDATE_CONFIG = "/install_instance/update_config/";
export const DIRS_WRITABLE = "/install_instance/dirs_writable/";
export const END_UPDATE = "/install_instance/end_update/";
export const CONFIG_DATA = "/install_instance/config_data/";

export class Routes {
    getInstallInstanceUpdateConfig() {
        return Keestash.Main.getApiHost() + UPDATE_CONFIG;
    }

    getInstallInstanceDirsWritable() {
        return Keestash.Main.getApiHost() + DIRS_WRITABLE;
    }

    getInstallInstanceEndUpdate() {
        return Keestash.Main.getApiHost() + END_UPDATE;
    }

    getConfigData() {
        return Keestash.Main.getApiHost() + CONFIG_DATA;
    }
}