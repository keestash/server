import Formula from "../../../../lib/js/src/Formula";
import AppStorage from "../../../../lib/js/src/AppStorage";
import Router from "../../../../lib/js/src/Router";
import modal from "../../../../lib/js/src/UI/modal";
import Input from "../../../../lib/js/src/UI/Input";

(function () {
    if (!Keestash.Login) {
        Keestash.Login = {};
    }

    Keestash.Login = {
        member: null,
        handleSubmit: function () {
            let that = this;

            $("#login").submit(function (event) {

                event.preventDefault();
                that.changeButtonState(false);
                let formula = new Formula();
                let appStorage = new AppStorage();

                let user = $("#username").val();
                let password = $("#password").val();
                let router = new Router();

                if (user === "") {
                    Input.invalid("#username");
                    that.changeButtonState(true);
                    return;
                }

                if (password === "") {
                    Input.invalid("#password");
                    that.changeButtonState(true);
                    return
                }

                let data = {
                    'user': user
                    , 'password': password
                };

                formula.post(
                    Keestash.Main.getApiHost() + "/login/submit/"
                    , data
                    , function (html, status, xhr) {
                        let object = JSON.parse(html);
                        let result_object = null;

                        if (1000 in object) {
                            result_object = object[1000];
                            let routeTo = result_object['routeTo'];
                            let token = xhr.getResponseHeader('api_token');
                            let userHash = xhr.getResponseHeader('user_hash');

                            appStorage.storeAPICredentials(
                                token
                                , userHash
                            );

                            appStorage.logCredentials();

                            router.routeTo(routeTo);
                            return;
                        } else if (2000 in object) {
                            result_object = object[2000];
                            modal.miniModal(result_object['message']);
                            appStorage.clearAPICredentials();
                        }

                        if (result_object === null) {
                            modal.miniModal("There was an error. Please try again or contact our support");
                            appStorage.clearAPICredentials();
                        }
                        that.changeButtonState(true);
                    }
                    , function (html, status, xhr) {
                        modal.miniModal("There was an error. Please try again or contact our support")
                        appStorage.clearAPICredentials();
                        that.changeButtonState(false);
                    }
                );
            });
        },
        changeButtonState: function (enable) {

            let button = $("#tl__login__button");

            button.addClass("disabled");
            if (true === enable) {
                button.removeClass("disabled");
            }
        }
    }
})();

$(document).ready(function () {
    Keestash.Login.handleSubmit();
});

