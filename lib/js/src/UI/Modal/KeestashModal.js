import $ from "jquery";
import 'bootstrap/dist/js/bootstrap';

export class KeestashModal {

    constructor(
        templateLoader
        , templateParser
    ) {
        this.templateLoader = templateLoader;
        this.templateParser = templateParser;
        this.modal = null;
    }

    async show(
        templateName
        , header
        , buttonText
        , negativeButtonText
        , description
        , eventName
    ) {
        const _this = this;
        await this.templateLoader.load(true);
        const templates = await this.templateLoader.read();
        const template = templates[templateName];

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
