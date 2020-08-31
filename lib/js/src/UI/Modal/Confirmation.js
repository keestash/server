import {KeestashModal} from "./KeestashModal";

export class Confirmation extends KeestashModal {
    constructor(
        templateLoader
        , templateParser
    ) {
        super(templateLoader, templateParser);
    }

    async show(header, buttonText, negativeButtonText, description, eventName) {
        return super.show('confirmation', header, buttonText, negativeButtonText, description, eventName);
    }
}
