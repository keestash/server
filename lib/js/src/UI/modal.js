import $ from 'jquery';

import '../semantic/semantic.min';
import Handlebars from 'handlebars';

export default {

    miniModal: function (description) {
        const x = '<div class="ui mini modal"><div class="image content"><div class="description">{{description}}</div></div></div>';
        let template = Handlebars.compile(x);

        let rendered = template({
            'description': description
        });
        $(rendered).modal('show');
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