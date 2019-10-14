import $ from "jquery";

export default {
    observer: []
    , registerObserver: function (listener) {
        this.observer.push(listener);
    }
    , notify: function (imageSource) {
        $(this.observer).each(function (key, value) {
            value(imageSource);
        });
    }
}