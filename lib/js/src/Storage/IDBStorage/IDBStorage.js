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
import localforage from "localforage";

export class IDBStorage {

    constructor(dbName) {
        this.store = localforage.createInstance({
            name: dbName
            , version: 1
            , storeName: dbName
        });
    }

    async add(key, template) {
        await this.store.setItem(key, template)
    }

    async clear() {
        await this.store.clear()
    }

    async getAll() {
        const result = {};

        const keys = await this.store.keys();

        for (let key in keys) {
            if (keys.hasOwnProperty(key)) {
                let name = keys[key];
                result[name] = await this.store.getItem(name);
            }
        }

        return result;
    }

    async addAll(templates) {
        for (let name in templates) {
            if (templates.hasOwnProperty(name)) {

                await this.add(
                    name
                    , templates[name]
                );

            }
        }

    }

}