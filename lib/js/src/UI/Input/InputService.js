import $ from "jquery";

export class InputService {

    constructor() {
        this.invalidColor = '#CC7F7F';
    }

    invalid(elementName) {
        $(elementName).css('border-color', this.invalidColor);
        this.shake(elementName);
        this.addRevertListener(elementName);
    }

    shake(element) {
        element.each(() => {

            let intShakes = 7;
            let intDistance = 7;
            let intDuration = 10;
            $(this).css("position", "relative");
            for (let x = 1; x <= intShakes; x++) {
                $(this).animate({left: (intDistance * -1)}, (((intDuration / intShakes) / 4)))
                    .animate({left: intDistance}, ((intDuration / intShakes) / 2))
                    .animate({left: 0}, (((intDuration / intShakes) / 4)));
            }
        });
        element.css("border-color", this.invalidColor);
    }

    addRevertListener(elementName) {
        $(elementName).on("input", () => {
            $(elementName).css('border-color', '');
            $(elementName).off("input");
        });
    }
}
