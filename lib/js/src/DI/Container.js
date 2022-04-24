/**
 * Keestash
 *
 * Copyright (C) <2020> <Dogan Ucar>
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
export class Container {

    constructor() {
        this.container = {};
        this.cache = {};
    }

    register(name, callback) {
        this.container[name] = callback;
    }

    query(name) {
        if (null !== (this.cache[name] || null)) {
            return this.cache[name];
        }

        if (!this.container.hasOwnProperty(name)) {
            throw 'Error: ' + name + ' not found';
        }
        const callBack = this.container[name];
        const object = callBack(this);

        this.cache[name] = object;
        return object;
    }
}
