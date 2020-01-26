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
import Formula from "../../../../lib/js/src/Formula";

(function () {
    if (!Keestash.AllUsers) {
        Keestash.AllUsers = {};
    }

    Keestash.AllUsers = {
        init: function () {
            let formula = new Formula();

            $("#tl__add__users__button").click(function () {
                $('.ui.modal')
                    .modal({
                        inverted: true
                        , onShow: function () {
                        }
                        , onApprove: function (element) {
                            var userName = $("#tl__user__name").val();
                            var firstName = $("#tl__first__name").val();
                            var lastName = $("#tl__last__name").val();
                            var email = $("#tl__email").val();
                            var phone = $("#tl__phone").val();
                            var password = $("#tl__password").val();
                            var passwordRepeat = $("#tl__password__repeat").val();
                            var website = $("#tl__website__name").val();

                            // TODO validate user input

                            formula.post(
                                Keestash.Main.getApiHost() + "/users/add"
                                , {
                                    'user_name': userName
                                    , 'first_name': firstName
                                    , 'last_name': lastName
                                    , 'email': email
                                    , 'phone': phone
                                    , 'password': password
                                    , 'password_repeat': passwordRepeat
                                    , 'website': website
                                }
                                , function (response, status, xhr) {
                                    location.reload();
                                }
                                , function (response, status, xhr) {
                                    alert("error");
                                }
                            );
                        }
                    })
                    .modal('show');
                ;
            });
        }
    }
})();
$(document).ready(function () {
    Keestash.AllUsers.init();
});