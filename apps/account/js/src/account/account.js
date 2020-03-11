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
import modal from "../../../../../lib/js/src/UI/modal";

(function () {
    if (!Keestash.Account) {
        Keestash.Account = {};
    }

    Keestash.Account = {
        init: function () {
            let formula = new Formula();

            $("#tl__pi__picture").click(function (e) {
                e.stopImmediatePropagation();
                e.preventDefault();
                $("#tl__upload__pp").focus().trigger("click");
            });

            $("#tl__pi__upload__profile__picture").click(function () {
                $("#tl__upload__pp").focus().trigger("click");
            });

            $("#tl__pi__remove__profile__picture").click(function () {

                let userId = $("#tl__pi__user__id").val();
                formula.post(
                    Keestash.Main.getApiHost() + "/account/profile/image/delete/"
                    , {'user_id': userId}
                    , function (response, status, xhr) {
                        let object = JSON.parse(response);
                        let message = null;
                        let src = $("#pi-data-node").attr("data-default-image");
                        $("#tl__pi__picture").attr("src", src);
                        Keestash.Observer.ProfileImage.notify(src);

                        if (1000 in object) {
                            message = object[1000]['message'];
                        } else if (2000 in object) {
                            message = object[2000]['message'];
                        }
                        modal.miniModal(message);
                    }
                    , function (response, status, xhr) {
                        modal.miniModal(response);
                    }
                );

            });

            $('#tl__upload__pp').on('change', function () {
                let myFile = $('#tl__upload__pp').prop('files');

                let file = myFile[0];

                if (file) {
                    // create reader
                    let reader = new FileReader();
                    reader.readAsDataURL(file);
                    reader.onload = function (e) {
                        let src = e.target.result;
                        let userId = $("#tl__pi__user__id").val();

                        $("#tl__pi__picture").attr("src", src);

                        formula.post(
                            Keestash.Main.getApiHost() + "/account/profile/image/update/"
                            , {
                                'image': src
                                , 'user_id': userId
                            }
                            , function (response, status, xhr) {
                                let object = JSON.parse(response);
                                let message = null;

                                Keestash.Observer.ProfileImage.notify(src);
                                modal.miniModal("updated");

                                if (1000 in object) {
                                    message = object[1000]['message'];
                                } else if (2000 in object) {
                                    message = object[2000]['message'];
                                }
                                modal.miniModal(message);
                            }
                            , function (response, status, xhr) {
                                alert("error");
                            }
                        );

                    };
                }

            });


            $("#tl__pi__form").submit(function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                let firstName = $("#tl__pi__first__name").val();
                let lastName = $("#tl__pi__last__name").val();
                let userId = $("#tl__pi__user__id").val();
                let email = $("#tl__pi__email").val();
                let phoneNumber = $("#tl__pi__phone__number").val();
                let website = $("#tl__pi__website").val();

                formula.post(
                    Keestash.Main.getApiHost() + "/account/profile/update/"
                    , {
                        'first_name': firstName
                        , "last_name": lastName
                        , "user_id": userId
                        , "email": email
                        , "phone_number": phoneNumber
                        , "website": website
                    }
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
    Keestash.Account.init();
});