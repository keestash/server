import $ from "jquery";

export default {
    getValue: function (name) {
        const dataNode = $("#data-node");
        return dataNode.attr(name);
    }
}