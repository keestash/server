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

const API_TOKEN = "token.api";
const USER_HASH = "hash.api";
const LOCALE = "locale";
const LANGUAGE = "language";

export class AppStorage extends LocalStorage {

    storeToken(value) {
        this.set(API_TOKEN, value);
    };

    storeUserHash(value) {
        this.set(USER_HASH, value);
    };

    storeLocale(locale) {
        this.set(LOCALE, locale);
    }

    storeLanguage(language) {
        this.set(LANGUAGE, language);
    }

    storeAPICredentials(token, userHash) {
        this.storeToken(token);
        this.storeUserHash(userHash);
    };

    getToken() {
        return this.get(API_TOKEN, null);
    };

    getUserHash() {
        return this.get(USER_HASH, null);
    };

    getLocale() {
        return this.get(LOCALE, null);
    };

    getLanguage() {
        return this.get(LANGUAGE, null);
    };

    logCredentials() {
        console.log(
            this.getToken() + ' ' + this.getUserHash()
        )
    };

    validToken() {
        return "" !== this.getToken() && null !== this.getToken();
    };


    validUserHash() {
        return "" !== this.getUserHash() && null !== this.getUserHash();
    };


    isValid() {
        return this.validToken() && this.validUserHash();
    };

    deleteToken() {
        this.storeToken(null);
    };

    deleteUserHash() {
        this.storeUserHash(null);
    };

    clearAPICredentials() {
        this.deleteToken();
        this.deleteUserHash();
    }

}
