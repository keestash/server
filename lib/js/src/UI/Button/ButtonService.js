export class ButtonService {

    disable(button, enable) {

        button.addClass("disabled");
        if (true === enable) {
            button.removeClass("disabled");
        }
    }
}
