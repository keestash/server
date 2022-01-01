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
import './init';
import 'popper.js/dist/popper';
import 'bootstrap/dist/js/bootstrap.bundle.min';
import {Container} from "./DI/Container";
import {StartUp} from "./StartUp";
import _ from "lodash";
import {DropDownButton} from "./UI/DropDown/DropDownButton";

export const EVENT_NAME_APP_NAVIGATION_CLICKED = 'clicked.navigation.app.name.event';

// noinspection JSUndefinedPropertyAssignment
global.jQuery = global.$ = require('jquery');

export const EVENT_NAME_SETTINGS_CLICKED = 'clicked.settings.name.event';
export const EVENT_NAME_ACTION_BAR_ITEM_CLICKED = 'clicked.item.bar.action.name.event';

(() => {
    Keestash.Main = {

        container: null,
        templates: null,
        strings: null,

        init: () => {
            console.warn('please remove me (Keestash.Main.init)');
            // silence is golden
        },

        getContainer: () => {
            console.warn('please remove me (Keestash.Main.getContainer)');
            if (null !== Keestash.Main.container) {
                return Keestash.Main.container;
            }

            const startUp = new StartUp(
                new Container()
            );
            startUp.setUp();
            Keestash.Main.container = startUp.getContainer();
            return Keestash.Main.container;
        },

        setAppNavigationListener(callable) {
            console.warn('please remove me (setAppNavigationListener)');
        }

    };

})();

window.addEventListener(
    'DOMContentLoaded'
    , (event) => {
        addAppNavigationListener();
        addTopMenuListener();
        addActionBarListener();
    }
)
$(document).ready(
    async () => {
        Keestash.Main.init();
    }
);

function addActionBarListener() {
    const dropDownButton = new DropDownButton(
        'action__bar__button'
        , 'action-bar-inner-dropdown'
    );
    dropDownButton.listen(EVENT_NAME_ACTION_BAR_ITEM_CLICKED);
}

function addTopMenuListener() {
    const dropDownButton = new DropDownButton(
        'main-menu-dropdown-button'
        , 'main-menu-dropdown-area'
    );
    dropDownButton.listen();
}

function addAppNavigationListener() {
    const appNavigation = document.getElementById('app-navigation');
    if (appNavigation === null || typeof appNavigation === 'undefined') return;
    const list = appNavigation.getElementsByTagName('ul')[0];
    if (list === null || typeof list === 'undefined') return;
    const allItems = list.getElementsByTagName('li');
    if (allItems === null || typeof allItems === 'undefined') return;

    for (let i = 0; i < allItems.length; i++) {
        const item = allItems[i];

        if (false === item.classList.contains('clickable-navigation-item')) {
            continue;
        }
        const triggerEvent = () => {
            const event = new CustomEvent(
                EVENT_NAME_APP_NAVIGATION_CLICKED
                , {detail: item}
            );

            document.dispatchEvent(event);
        }
        item.addEventListener(
            'click'
            , (e) => {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                _.debounce(triggerEvent, 500)();
            }
        );
    }
}