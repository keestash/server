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