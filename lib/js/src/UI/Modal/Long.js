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
import $ from "jquery";
import 'bootstrap/dist/js/bootstrap';
import 'formvalidation/dist/js/formValidation'
import {EVENT_NAME_NEW_FOLDER} from "../../../../../apps/password_manager/js/src/password_manager/Node/Folder/Listener/FolderListener";

export class Long {

    constructor(
        templateLoader
        , templateParser
    ) {
        this.templateLoader = templateLoader;
        this.templateParser = templateParser;
        this.modal = null;
    }

    async show(
        header
        , buttonText
        , negativeButtonText
        , description
        , eventName
    ) {
        const _this = this;
        this.templateLoader.load(true);
        const templates = await this.templateLoader.read();
        const template = templates['long'];

        let parsedTemplate = this.templateParser.parse(
            template
            , {
                header: header
                , description: description
                , buttonText: buttonText
                , negativeButtonText: negativeButtonText
            }
        );

        this.modal = $(parsedTemplate);
        this.modal.on(
            'shown.bs.modal'
            , (e) => {

                _this.modal.find(".btn-primary").click(
                    () => {
                        _this.clearMessage();
                        return $(document).trigger(
                            eventName
                            , [
                                this.modal
                            ]
                        );
                    });

            })

        this.modal.modal(
            {
                backdrop: false
                , keyboard: true
                , focus: true
                , show: true
            }
        );

    }

    showError(message) {
        this.showMessage(message, "alert-danger")
    }

    showSuccess(message) {
        this.showMessage(message, "alert-success")
    }

    showMessage(message, clazz) {
        this.modal.find("#response__text")
            .text(message)
            .addClass(clazz)
            .addClass("d-flex")
            .removeClass("d-none");
    }

    clearMessage() {
        this.modal.find("#response__text")
            .text("")
            .addClass("d-none")
            .removeClass("d-flex")
            .removeClass("alert-danger");
    }


}
