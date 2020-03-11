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
import Formula from "../../../../../lib/js/src/Formula";

(function () {
    if (!Keestash.Security) {
        Keestash.Security = {};
    }
    Keestash.Security = {

        init: function () {
            let formula = new Formula();


            $("#tl__security__password__form").submit(function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                let userId = $("#tl__sc__user__id").val();
                let currentPassword = $("#tl__sc__current__password").val();
                let password = $("#tl__sc__password").val();
                let passwordRepeat = $("#tl__sc__password__repeat").val();

                let values = {};
                values["user_id"] = userId;
                values["current_password"] = currentPassword;
                values["password"] = password;
                values["password_repeat"] = passwordRepeat;

                formula.post(
                    Keestash.Main.getApiHost() + "/security/password/update/"
                    , values
                    , function (response, status, xhr) {
                        alert("updated!");
                    }
                    , function (response, status, xhr) {
                        alert("error");
                    }
                );

            });

        }
    }
})();
$(document).ready(function () {
    Keestash.Security.init();
});