import $ from "jquery";

export default {
    SELECTORS: {
        APP_NAVIGATION_CONTAINER: "#upper__navigation__wrapper"
    },
    CONSTANTS: {
        FADE_IN_TIME: 9000
        , ATTRIBUTE_NAME_ID: "data-id"
    },
    part: function () {

    }
    , register: function (listener) {
        this.part = listener;
    }
    , run: function (data) {
        const appNavigationClick = this;
        (function () {
            $("#data-node").attr("data-selected-id", data);
            appNavigationClick.part(data);
        })();
    }

    , reattach: function () {
        const navigationClickListener = this;
        $(this.SELECTORS.APP_NAVIGATION_CONTAINER + " a").each(function (index, value) {
            const id = $(value).attr(navigationClickListener.CONSTANTS.ATTRIBUTE_NAME_ID);
            $(value).off("click");
            $(value).click(function () {
                navigationClickListener.run(id);
            });
        });
        Keestash.Observer.AppNavigationItemSubMenu.reattach();
    }
}