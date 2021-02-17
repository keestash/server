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
import {AXIOS} from "../../../../lib/js/src/StartUp";

(function () {
    if (!Keestash.AppsApp) {
        Keestash.AppsApp = {};
    }

    Keestash.AppsApp = {

        listen: function () {
            const diContainer = Keestash.Main.getContainer();

            $(".apps__app__checkbox").on("click", function () {
                const checked = $(this).prop("checked");
                const appId = $(this).attr("data-app-id");
                const axios = diContainer.query(AXIOS);

                axios.post(
                    routes.getAppsUpdate(appId)
                    , {
                        "activate": true === checked
                        , "app_id": appId
                    },
                ).then((r) => console.log(r));

            });
        }

    }

})
();

$(document).ready(function () {
    Keestash.AppsApp.listen();
});

