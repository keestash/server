export default {
    SELECTORS: {
        APP_NAVIGATION_CONTAINER: "#upper__navigation__wrapper"
    }
    , removePartFromUI: function (id) {
        const navigationSubMenuClick = this;
        if ($(navigationSubMenuClick.SELECTORS.APP_NAVIGATION_CONTAINER).children().length <= 1) return false;
        $(navigationSubMenuClick.SELECTORS.APP_NAVIGATION_CONTAINER + " a").each(
            function (index, value) {
                const selectedId = $(value).attr("data-id");
                if (id === selectedId) {
                    $(value).fadeOut(300, function () {
                        $(value).remove();
                    });
                }
            });
    }
    , clickFirstPart: function () {
        $(this.SELECTORS.APP_NAVIGATION_CONTAINER).children().first()[0].click();
    }
}