import $ from "jquery";

export class Long {

    init(header, buttonText, description, eventName) {
        const x = '<div class="ui long test modal">' +
            '<div class="header">' + header + '</div>' +
            '<div class="description">' + description + '</div>' +
            '<div class="actions">' +
            '<div class="ui primary approve button">' + buttonText + '' +
            '<i class="right chevron icon"></i>' +
            '</div>' +
            '</div>' +
            '</div>';
        $(x)
            .modal(
                {
                    onApprove: function () {
                        $(document).trigger(eventName);
                    }
                    , closable: true
                    , allowMultiple: false
                }
            )
            .modal("show")
        ;
    }
}