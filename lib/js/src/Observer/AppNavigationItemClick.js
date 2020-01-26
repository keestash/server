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