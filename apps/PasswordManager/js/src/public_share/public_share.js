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
import {PublicShare} from "./PublicShare";
import {MINI_MODAL, REQUEST} from "../../../../../lib/js/src/StartUp";
import {Routes} from "../Public/Routes";

(() => {
    if (!Keestash.Apps.PasswordManager) {
        Keestash.Apps.PasswordManager = {};
    }

    if (!Keestash.Apps.PasswordManager.SinglePassword) {
        Keestash.Apps.PasswordManager.SinglePassword = {};
    }
    Keestash.Apps.PasswordManager.SinglePassword = {
        init: () => {
            const diContainer = Keestash.Main.getContainer();

            const publicShare = new PublicShare(
                diContainer.query(REQUEST)
                , diContainer.query(MINI_MODAL)
                , new Routes()
            );
            publicShare.init();
        }
    }
})();


$(document).ready(
    async () => {
        Keestash.Apps.PasswordManager.SinglePassword.init();
    }
);
