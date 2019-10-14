import $ from "jquery";
import ProfileImage from "./Observer/ProfileImage";
import './init';
import {KEY_CODE_ENTER} from "./Constant/Constant";
import DataNode from "./Data/DataNode";
import Parser from "./UI/Template/Parser";

// noinspection JSUndefinedPropertyAssignment
global.jQuery = global.$ = require('jquery');

(function () {

    Keestash.Main = {
        SELECTORS: {
            RIGHT_MENU: "#right_menu"
            , PROFILE_IMAGE: "#tl__pp__top__img"
            , UPPER_NAVIGATION_WRAPPER: "#upper__navigation__wrapper"
            , APP_NAVIGATION_VALUE: '#app-navigation-value'
        },
        basicHandle: function () {
            this.setupDropdown();
            this.attachGroupListener();
            this.attachTrashButtonAction();
        },
        rightMenuAvailable: function () {
            return ($(Keestash.Main.SELECTORS.RIGHT_MENU).length) > 0;
        },
        setupDropdown: function () {
            if (false === Keestash.Main.rightMenuAvailable()) return;
            $(Keestash.Main.SELECTORS.RIGHT_MENU).dropdown();
            ProfileImage.registerObserver(
                function (imageSource) {
                    $(Keestash.Main.SELECTORS.PROFILE_IMAGE).attr("src", imageSource);
                }
            );

        },

        sideBar: function (context) {
            const sideBarTemplate = DataNode.getValue("data-side-bar-template");
            const sideBar = Parser.parse(sideBarTemplate, context);

            $(sideBar)
                .sidebar('setting', 'transition', 'overlay')
                .sidebar('toggle')
            ;

        },
        getHost: function () {
            return $("#data-node").attr("data-host");
        },

        getApiHost: function () {
            return $("#data-node").attr("data-api-host");
        },

        attachGroupListener: function () {

            $.each(
                $(Keestash.Main.SELECTORS.UPPER_NAVIGATION_WRAPPER).children()
                , function (i, v) {

                    $(this).off('click').on(
                        "click"
                        , function () {
                            const id = $(this).attr("data-id");
                            Keestash.Observer.AppNavigationItemClick.run(id);
                        })

                });
        },

        setKeyUp: function () {

            $(Keestash.Main.SELECTORS.APP_NAVIGATION_VALUE).keyup(
                function (e) {
                    if (e.keyCode !== KEY_CODE_ENTER) {
                        return;
                    }
                    const input = $(this).val();

                    let values = {};
                    values["navigation-input"] = input;

                    Keestash.Observer.NewPart.run(values);

                    $(this).fadeIn(300, function () {
                        $(this).val('');
                    });

                });
        },

        attachTrashButtonAction: function () {
            const element = $(".tl__trash__button");
            element.off('click').click(function (event) {
                event.preventDefault();
                const id = $(this).attr("data-id");
                Keestash.Observer.AppNavigationItemSubMenu.run(id);
            });

        },

        setNewPartListener: function (method) {
            Keestash.Observer.NewPart.register(method);
        },
        setAppNavigationSubMenuListener: function (method) {
            Keestash.Observer.AppNavigationItemSubMenu.register(method);
        },
        setAppNavigationItemClickListener: function (method) {
            Keestash.Observer.AppNavigationItemClick.register(method);
        },
        deattachListeners: function () {
            this.setAppNavigationItemClickListener(function () {

            });
            this.setAppNavigationSubMenuListener(function () {

            });
            this.setNewPartListener(function () {

            });
        }
    };

    Keestash.Main.Logger = {
        log: function (message) {
            console.log(this.dateFormat());
            console.log(message);
        }
        , error: function (message) {
            console.error(this.dateFormat());
            console.error(message);
        }
        , warn: function (message) {
            console.warn(this.dateFormat());
            console.warn(message);
        }
        , dateFormat: function () {
            let formattedDate = new Date();
            let d = formattedDate.getDate();
            let m = (formattedDate.getMonth()) + 1;
            if (m < 10) {
                m = '0' + m;
            }
            let y = formattedDate.getFullYear();
            let h = formattedDate.getHours();
            let i = formattedDate.getMinutes();
            let s = formattedDate.getSeconds();

            return (y + '-' + m + '-' + d + ' ' + h + ':' + i + ':' + s);
        }
    };

})();

$(document).ready(function () {
    Keestash.Main.basicHandle();
    Keestash.Main.setKeyUp();
});
