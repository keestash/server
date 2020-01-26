/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
import $ from "jquery";
import ProfileImage from "./Observer/ProfileImage";
import './init';
import {KEY_CODE_ENTER} from "./Constant/Constant";
import {ActionBar} from "./UI/ActionBar/ActionBar";

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

        initActionBar: function (moduleList, id) {
            const actionBar = new ActionBar(moduleList, id);
            actionBar.register();
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

})();

$(document).ready(function () {
    Keestash.Main.basicHandle();
    Keestash.Main.setKeyUp();
});
