import {KeestashModal} from "./KeestashModal";

export class Mini extends KeestashModal {

    constructor(
        templateLoader
        , templateParser
    ) {
        super(templateLoader, templateParser);
    }

    show(header, buttonText, negativeButtonText, description) {
        return super.show('mini', header, buttonText, negativeButtonText, description);
    }

}
