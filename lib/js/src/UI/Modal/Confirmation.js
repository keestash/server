import {KeestashModal} from "./KeestashModal";

export class Confirmation extends KeestashModal {
    constructor(
        templateLoader
        , templateParser
    ) {
        super(templateLoader, templateParser);
    }

    show(header, buttonText, negativeButtonText, description) {
        return super.show('confirmation', header, buttonText, negativeButtonText, description);
    }
}
