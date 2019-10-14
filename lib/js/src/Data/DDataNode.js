export class DDataNode {

    constructor(id) {
        this.element = $("#" + id);
    }

    getValue(attribute) {
        return this.element.attr(attribute);
    }
}