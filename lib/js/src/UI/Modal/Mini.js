import {KeestashModal} from "./KeestashModal";

export class Mini extends KeestashModal {

    constructor(
        templateLoader
        , templateParser
    ) {
        super(templateLoader, templateParser);
    }

    async show(header, buttonText, negativeButtonText, description, eventName) {
        return super.show('mini', header, buttonText, negativeButtonText, description, eventName);
    }

}
