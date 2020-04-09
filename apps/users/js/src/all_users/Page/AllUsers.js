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
export class AllUsers {

    constructor(
        request
        , stringLoader
        , templateLoader
        , parser
        , routes
    ) {
        this.request = request;
        this.stringLoader = stringLoader;
        this.templateLoader = templateLoader;
        this.parser = parser;
        this.routes = routes;
    }

    async handle() {
        // TODO remove before going live
        await this.stringLoader.load(true);
        await this.templateLoader.load(true);
        await this.addButtonListener();
    }


    async addButtonListener() {
        const _this = this;
        const button = $("#all__users__add__new__user");

        const strings = await this.stringLoader.read();
        const userStrings = JSON.parse(strings.users);
        const templates = await this.templateLoader.read();

        button.click(() => {

            const _this = this;
            const p = _this.parser.parse(
                templates["new-user-add"]
                , userStrings.strings
            );

            $(p).modal({
                inverted: true
                , onApprove: function (element) {
                    const userName = $("#tl__user__name").val();
                    const firstName = $("#tl__first__name").val();
                    const lastName = $("#tl__last__name").val();
                    const email = $("#tl__email").val();
                    const phone = $("#tl__phone").val();
                    const password = $("#tl__password").val();
                    const passwordRepeat = $("#tl__password__repeat").val();
                    const website = $("#tl__website__name").val();

                    // TODO validate user input

                    _this.request.post(
                        _this.routes.getAddUser()
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