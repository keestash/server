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
import {LocalStorage} from "./LocalStorage/LocalStorage";

const CREATE_DATE = "_create_date";
const TEMPORARY_STORAGE_TIME = 1000 * 60 * 1;

export class TemporaryStorage extends LocalStorage {

    get(key, defaultValue = null) {
        const expireDate = super.get(key + CREATE_DATE, null);
        if (null === expireDate) return defaultValue;
        const value = super.get(key, defaultValue);
        if (null === value) return defaultValue;

        if (((new Date) - expireDate) < TEMPORARY_STORAGE_TIME) {
            super.remove(key);
            return defaultValue;
        }

        return value;
    }

    set(key, value) {
        super.set(key, value);
        super.set(key + CREATE_DATE, new Date());
    }
}