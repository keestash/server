import Part from "../UI/Part/Part";
import $ from "jquery";

export default {
    SELECTORS: {
        APP_NAVIGATION_CONTAINER: "#upper__navigation__wrapper"
    },
    part: function () {

    }
    , register: function (listener) {
        this.part = listener;
    }
    , run: function (id) {
        const navigationSubMenuClick = this;
        (function () {

            $("#tl__delete__left__menu__item__modal")
                .modal({
                    onApprove: function () {
                        navigationSubMenuClick.part(id);
                        Part.clickFirstPart();
                        Part.removePartFromUI(id);
                    }
                })
                .modal('show')
            ;


        })();
    }
    , reattach: function () {
        const navigationSubMenuClick = this;
        $(navigationSubMenuClick.SELECTORS.APP_NAVIGATION_CONTAINER + " a i").each(
            function (index, value) {
                const id = $(value).attr("data-id");
                $(value).off("click");
                $(value).click(function () {
                    navigationSubMenuClick.run(id);
                });
            });
    }

}