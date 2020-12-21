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
import {ActionBar} from "./UI/ActionBar/ActionBar";
import 'popper.js/dist/popper';
import 'bootstrap/dist/js/bootstrap.bundle.min';
import {Container} from "./DI/Container";
import {StartUp, STRING_LOADER, TEMPLATE_LOADER} from "./StartUp";

// noinspection JSUndefinedPropertyAssignment
global.jQuery = global.$ = require('jquery');

(() => {
    Keestash.Main = {

        container: null,
        templates: null,
        strings: null,
        init: () => {

        },

        getContainer: () => {

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

        initActionBar: (moduleList, id) => {
            $('.dropdown-toggle').dropdown();
            const actionBar = new ActionBar(moduleList, id);
            actionBar.register();
        },

        readAssets: async (loadFirst = false) => {

            if (
                Keestash.Main.templates !== null
                && Keestash.Main.strings !== null
                && false === loadFirst
            ) {
                return [Keestash.Main.templates, Keestash.Main.strings];
            }

            const diContainer = Keestash.Main.getContainer();
            const stringLoader = diContainer.query(STRING_LOADER);
            const templateLoader = diContainer.query(TEMPLATE_LOADER);

            if (true === loadFirst) {
                await templateLoader.load(true);
                await stringLoader.load(true);
            }

            const templates = await templateLoader.read();
            const strings = await stringLoader.read();

            Keestash.Main.templates = templates;
            Keestash.Main.strings = strings;

            return [templates, strings];
        },

        setAppNavigationListener(callable) {
            $("#app-navigation .list-group-item").off("click").click(
                (event) => {
                    event.preventDefault();

                    const element = $(event.target);
                    const id = element.attr("id");

                    callable(id);
                }
            )
        }

    };

})();

$(document).ready(async () => {
    Keestash.Main.init();
});
