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
import $ from "jquery";
import {EVENT_BIND_ON, EVENT_NAME_CREDENTIAL_CHANGE} from "./Event/CredentialChange";

export class Text {

    constructor(id) {
        this.id = id;
        this.dataNodeId = "pwm__login__data__password";
        this.enabled = true;
    }

    init() {
        this.listen();
    }

    listen() {
        const _this = this;
        const element = $("#" + _this.id);
        element.change(function () {
            if (false === _this.isEnabled()) return;

            const value = element.val();
            const nodeId = $("#" + _this.dataNodeId).attr("data-login-id");

            if (typeof value === 'undefined') return;
            if (value === null) return;
            if (value === "") return;
            if (typeof nodeId === 'undefined') return;
            if (nodeId === null) return;
            if (nodeId === "") return;

            $(EVENT_BIND_ON).trigger(EVENT_NAME_CREDENTIAL_CHANGE);
        });
    }

    disable() {
        this.enabled = false;
    }

    enable() {
        this.enabled = true;
    }

    isEnabled() {
        return true === this.enabled;
    }
}
