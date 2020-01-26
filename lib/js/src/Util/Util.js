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
export class Util {
    /**
     * @deprecated use static instead
     * @param value
     * @returns {boolean}
     */
    isJson(value) {
        return Util.isJson(value);
    }

    static getExtension(filename) {
        const a = filename.split(".");
        if (a.length === 1 || (a[0] === "" && a.length === 2)) {
            return null;
        }
        return a.pop();
    }

    static isJson(value) {
        if (typeof value != 'string')
            value = JSON.stringify(value);

        try {
            JSON.parse(value);
            return true;
        } catch (e) {
            return false;
        }
    }

    static isSet(value) {
        return !(typeof value === 'undefined' || undefined === value || null === value);
    }
}