export class ButtonService {

    disable(button, enable) {

        button.addClass("disabled");
        button.disabled = true;
        if (true === enable) {
            button.removeClass("disabled");
            button.disabled = false;
        }
    }
}
