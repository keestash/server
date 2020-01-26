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
import Input from "../../../../lib/js/src/UI/Input";

export class Validator {
    isEmpty(val) {
        if (val === "") return true;
        if (val === null) return true;
        if (typeof val === 'undefined') return true;
        return false;
    }

    getValIfExists(name) {
        const element = $("#" + name);
        if (0 === element.length) return null;
        return element.val();
    }

    isValidSelect(val, id) {
        if (false === this.isEmpty(val)) return true;
        if (val === "enabled") return true;
        if (val === "disabled") return true;

        window.setTimeout(function () {
            Input.invalid("#" + id);
        }, 500);
        return false;
    }

    isValid(val, id) {

        if (this.isEmpty(val)) {

            window.setTimeout(function () {
                Input.invalid("#" + id);
            }, 500);

            return false;
        }

        return true;
    }
}