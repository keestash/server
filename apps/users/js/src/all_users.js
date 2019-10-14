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