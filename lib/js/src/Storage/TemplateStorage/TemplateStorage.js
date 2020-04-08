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
import {clear, get, keys, set} from 'idb-keyval';
import {Util} from "../../Util/Util";

export class TemplateStorage {

    async add(key, template) {
        await set(key, template);
    }

    async get(key) {
        if (null === Util.getExtension(key)) {
            key = key + ".twig";
        }
        return await get(key);
    }

    async clear() {
        await clear();
    }

    async getAll() {
        const templates = {};
        const _this = this;

        await keys().then(async (keys) => {

            for (let key in keys) {
                if (keys.hasOwnProperty(key)) {
                    let name = keys[key];

                    await _this.get(name).then(
                        (template) => {
                            name = name.replace(".twig", "");
                            templates[name] = template;
                        }
                    );

                }
            }

        });
        return templates;
    }

    async addAll(templates) {
        for (let name in templates) {
            if (templates.hasOwnProperty(name)) {
                this.add(name, templates[name]);
            }
        }
    }
}