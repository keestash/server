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

import Parser from "./Template/Parser";

export default {

    miniModal: function (description) {
        const template = '<div class="ui mini modal"><div class="image content"><div class="description">{{description}}</div></div></div>';
        const parsed = Parser.parse(
            template
            , {
                'description': description
            }
        );

        $(parsed).modal('show');
    },
    show: function (elementName, text) {
        $(elementName + ' .description').html(text);
        $(elementName).modal('show');
    }
    , longModal: function (header, buttonText, description, listener) {
        return;
        const x = '<div class="ui long test modal">' +
            '<div class="header">' + header + '</div>' +
            '<div class="description" id="long__modal__content">' + description + '</div>' +
            '<div class="actions">' +
            '<div class="ui primary approve button" id="long__modal__button">' + buttonText + '' +
            '<i class="right chevron icon"></i>' +
            '</div>' +
            '</div>' +
            '</div>';
        $(x)
            .modal("show");
    }
    , confirmationModal: function (header, question, approve) {
        const x = '<div class="ui basic modal">' +
            '  <div class="ui icon header">' +
            '    <i class="archive icon"></i>' +
            header +
            '  </div>' +
            '  <div class="content">' +
            '    <p>' + question + '</p>' +
            '  </div>' +
            '  <div class="actions">' +
            '    <div class="ui red basic cancel inverted button">' +
            '      <i class="remove icon"></i>' +
            '      No' +
            '    </div>' +
            '    <div class="ui green ok inverted button">' +
            '      <i class="checkmark icon"></i>' +
            '      Yes' +
            '    </div>' +
            '  </div>' +
            '</div>';
        $(x).modal({
            onApprove: approve
        })
            .modal('show')
        ;
    }
}
