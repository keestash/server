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
import {RESPONSE_CODE_NOT_OK, RESPONSE_CODE_OK} from "../../../../../lib/js/src/Backend/Request";

export const CONFIG_DATA = "config_data";
export const WRITABLE_DIRS = "writable_dirs";
export const READABLE_DIRS = "readable_dirs";
export const HAS_DATA_DIRS = "has_data_dirs";

export class Base {

    constructor(
        parentId
        , dimmerId
        , request
        , templateLoader
        , templateParser
        , keys
    ) {
        this.parent = $("#" + parentId);
        this.dimmer = $("#" + dimmerId);
        this.formula = request;
        this.templateLoader = templateLoader;
        this.templateParser = templateParser;
        this.dataNodeId = "install__instance__data__node";
        this.dataTemplatesId = "data-templates";
        this.dataStringsId = "data-strings";
        this.keys = keys;
    }

    handle() {
        const _this = this;
        const keys = _this.getKeys();
        const keyName = keys.name;
        _this.unbindForm();

        this.formula.get(
            keys.route
            , {}
            , async (e) => {

                const response = JSON.parse(e);

                if (RESPONSE_CODE_NOT_OK in response) {
                    _this.updateView("error during request");
                    return;
                }

                if (RESPONSE_CODE_OK in response) {

                    const raw = response[RESPONSE_CODE_OK]['messages'][keyName];
                    const rawStrings = response[RESPONSE_CODE_OK]['messages']["strings"];

                    const object = JSON.parse(raw);
                    const strings = JSON.parse(rawStrings);

                    const indices = Object.keys(object);
                    const indicesLength = indices.length;

                    await _this.templateLoader.load(true);
                    const templates = await _this.templateLoader.read();
                    const template = templates[keys.template_name];

                    if (0 === indicesLength) {
                        _this.updateView(strings.nothingToUpdate);
                        _this.triggerEvent();
                        return;
                    }

                    const contextData = {
                        ...{
                            objectData: object[indices[0]]
                        }
                        , ...strings
                    };


                    const parsed = _this.templateParser.parse(
                        template
                        , contextData
                    );

                    _this.updateView(parsed);
                    _this.initFormSubmit(strings);
                }

            }
            , function (e) {
                _this.updateView("error during request");
            }
        )
    }

    initFormSubmit(strings) {

    }

    unbindForm() {
        $("form").ready(function (e) {
            $(this).unbind("submit");
        })
    }

    triggerEvent() {
        $(document).trigger(this.getKeys().name);
    }


    updateView(string) {
        this.dimmer.remove();
        this.parent.append(string).hide().fadeIn(500);
    }

    getKeys() {
        return this.keys;
    }

    removeAllExceptFirst(element) {
        element.children().each((i, v) => {
            if (i !== 0)
                $(v).fadeOut(3000).remove();
        });
    }


}
