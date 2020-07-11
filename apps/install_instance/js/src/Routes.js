/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
import {Host} from "../../../../lib/js/src/Backend/Host";

export const UPDATE_CONFIG = "/install_instance/update_config/";
export const DIRS_WRITABLE = "/install_instance/dirs_writable/";
export const END_UPDATE = "/install_instance/end_update/";
export const CONFIG_DATA = "/install_instance/config_data/";
export const HAS_DATA_DIRS = "/install_instance/has_data_dirs/";

export class Routes {
    constructor() {
        this.host = new Host();
    }

    getInstallInstanceUpdateConfig() {
        return this.host.getApiHost() + UPDATE_CONFIG;
    }

    getInstallInstanceEndUpdate() {
        return this.host.getApiHost() + END_UPDATE;
    }

    getConfigData() {
        return this.host.getApiHost() + CONFIG_DATA;
    }

    getDirsWritableData() {
        return this.host.getApiHost() + DIRS_WRITABLE;
    }

    getHasDataDirs() {
        return this.host.getApiHost() + HAS_DATA_DIRS;
    }

}