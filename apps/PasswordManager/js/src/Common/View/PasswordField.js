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

export class PasswordField {

    constructor(id) {
        this.id = id;
    }

    show() {
        this.changeState(true);
    }

    hide() {
        this.changeState(false);
    }

    changeState(show) {
        const visible = true === show ? "1" : "0";
        const type = true === show ? "text" : "password";
        const readonly = true !== show;

        $(this.id).attr("data-credential-visible", visible);
        $(this.id).get(0).type = type;
        $(this.id).attr('readonly', readonly);
    }

    setValue(value) {
        $(this.id).val(value);
        $(this.id).attr("value", value);
    }

    getData(name) {
        return $(this.id).data(name);
    }

    isVisible() {
        return $(this.id).attr("data-credential-visible") === "1";
    }
}
