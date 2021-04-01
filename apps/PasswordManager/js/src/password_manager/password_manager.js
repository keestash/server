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
import $ from 'jquery';
import {NODE_ID_ROOT, STORAGE_ID_ROOT} from "./Node/Node";

import {BREADCRUMB_SERVICE, TEMPORARY_STORAGE} from "../../../../../lib/js/src/StartUp";
import {
    ACTION_BAR_CREDENTIAL,
    ACTION_BAR_FOLDER,
    Container,
    PASSWORD_LIST_SEARCH,
    PWM_NODE
} from "../Common/Container/Container";

(function () {
    if (!Keestash.Apps.PasswordManager) {
        Keestash.Apps.PasswordManager = {};
    }
    Keestash.Apps.PasswordManager = {
        init: async () => {

            const container = new Container();
            container.register();

            const diContainer = Keestash.Main.getContainer();
            const temporaryStorage = diContainer.query(TEMPORARY_STORAGE);
            const node = diContainer.query(PWM_NODE);
            const breadCrumbService = diContainer.query(BREADCRUMB_SERVICE);

            breadCrumbService.show();

            const rootId = temporaryStorage.get(
                STORAGE_ID_ROOT
                , NODE_ID_ROOT
            );

            node.loadDetails(
                rootId
            );

            Keestash.Main.setAppNavigationListener(
                (id) => {
                    node.loadDetails(id);
                }
            );


            Keestash.Main.initActionBar(
                [
                    diContainer.query(ACTION_BAR_CREDENTIAL)
                    , diContainer.query(ACTION_BAR_FOLDER)
                ]
                , "password__manager__add"
            );

            const searchPasswordList = diContainer.query(PASSWORD_LIST_SEARCH);
            searchPasswordList.init();
        },
    }
})();


$(document).ready(async () => {
    await Keestash.Apps.PasswordManager.init();
});
