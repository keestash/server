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