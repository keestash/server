export default {
    SELECTORS: {
        APP_NAVIGATION_CONTAINER: "#upper__navigation__wrapper"
    },
    CONSTANTS: {
        FADE_IN_TIME: 9000
    },
    part: function () {

    }
    , register: function (listener) {
        this.part = listener;
    }
    , run: function (data) {
        this.part(data);
    }
    , runUI: function (html) {
        $(this.SELECTORS.APP_NAVIGATION_CONTAINER).fadeIn(
            this.CONSTANTS.FADE_IN_TIME
            , function () {
                $(this).append(html);
            }
        );
        Keestash.Observer.AppNavigationItemClick.reattach();
    }
}