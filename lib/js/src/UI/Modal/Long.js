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
import Parser from "../Template/Parser";

export class Long {

    constructor() {
        this.template = '<div class="ui long test modal">' +
            '<div class="header">{{header}}</div>' +
            '<div class="description">{{description}}</div>' +
            '<div class="actions">' +
            '<div class="ui primary approve button">{{buttonText}}<i class="right chevron icon"></i>' +
            '</div>' +
            '</div>' +
            '</div>';
        this.element = null;
    }

    init(header, buttonText, description, eventName) {
        const _this = this;

        let parsedTemplate = Parser.parse(
            this.template
            , {
                header: header
                , description: description
                , buttonText: buttonText
            }
        );

        this.element = $(parsedTemplate);

        this.element
            .modal(
                {
                    onApprove: function () {
                        $(document).trigger(eventName);
                    }
                    , onShow: () => {

                    }
                    , onDeny: () => {
                        _this.element.remove();
                    }
                    , closable: true
                    , allowMultiple: false
                }
            )
            .modal("show")
        ;
    }

}