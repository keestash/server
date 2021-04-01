/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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
export class Login {

    /**
     *
     * @param {Axios} axios
     * @param routes
     * @param  emailValidator
     * @param {TemporaryStorage} temporaryStorage
     */
    constructor(
        axios
        , routes
        , emailValidator
        , temporaryStorage
    ) {
        this.axios = axios;
        this.routes = routes;
        this.emailValidator = emailValidator;
        this.temporaryStorage = temporaryStorage;
    }

    hideAndClose() {

    }

    init() {
        const loginFormWrapper = $("#loginform-wrapper");
        const modal = $("#emailAddressModal");
        const isDemo = loginFormWrapper.data("demo").length > 0;
        const demoSubmitted = "true" === this.temporaryStorage.get("demo-submitted", "false");
        const _this = this;

        if (false === isDemo) {
            modal.modal("hide");
            return;
        }
        if (true === demoSubmitted) {
            modal.modal("hide");
            return;
        }

        modal.modal();
        $("#send-demo-email").click(
            (event) => {
                event.preventDefault();
                const dangerAlert = $("#danger--alert");
                dangerAlert.removeClass("d-flex").addClass("d-none");

                const email = $("#demouser_email_address").val();

                if ("" === email || false === _this.emailValidator.isValidAddress(email)) {
                    dangerAlert.addClass("d-flex").removeClass("d-none");
                    return;
                }

                $("#loading--spinner").addClass("d-flex").removeClass("d-none");
                _this.axios.post(
                    _this.routes.getDemoUsersAdd()
                    , {
                        email: email
                    }
                ).then(
                    () => {
                        _this.temporaryStorage.set("demo-submitted", "true");
                        modal.modal("hide");
                    }
                );


            }
        )

    }
}