export const UPDATE_CONFIG = "/install_instance/update_config/";
export const DIRS_WRITABLE = "/install_instance/dirs_writable/";
export const END_UPDATE = "/install_instance/end_update/";
export const CONFIG_DATA = "/install_instance/config_data/";
export const HAS_DATA_DIRS = "/install_instance/has_data_dirs/";

export class Routes {
    getInstallInstanceUpdateConfig() {
        return Keestash.Main.getApiHost() + UPDATE_CONFIG;
    }

    getInstallInstanceEndUpdate() {
        return Keestash.Main.getApiHost() + END_UPDATE;
    }

    getConfigData() {
        return Keestash.Main.getApiHost() + CONFIG_DATA;
    }

    getDirsWritableData() {
        return Keestash.Main.getApiHost() + DIRS_WRITABLE;
    }

    getHasDataDirs() {
        return Keestash.Main.getApiHost() + HAS_DATA_DIRS;
    }

}