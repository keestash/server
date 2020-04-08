import "../../Thirdparty/eventPause/eventPause.min";

export class Handler {

    constructor(element) {
        this.element = element;
        this.events = [];
    }

    disable() {
        this.element.eventPause();
    }

    enable() {
        this.element.eventPause('active');
    }

}