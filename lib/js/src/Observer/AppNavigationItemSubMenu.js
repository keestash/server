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