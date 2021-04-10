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
import {Util} from "../../../../../../lib/js/src/Util/Util";
import $ from "jquery";
import 'jquery-ui-dist/jquery-ui';
import {RESPONSE_CODE_OK} from "../../../../../../lib/js/src/Backend/Request";
import "./ContextMenu/ContextMenu";
import {ContextMenu} from "./ContextMenu/ContextMenu";
import {AddToOrganization} from "./ContextMenu/Organization/AddToOrganization";
import glob from "glob";

export const NODE_TYPE_CREDENTIAL = "credential";
export const NODE_TYPE_FOLDER = "folder";
export const NODE_TYPE_SHARE = "share";
export const NODE_ID_ROOT = "root";

export const STORAGE_ID_ROOT = "root.id.storage";

export class Node {
    constructor(
        routes
        , appStorage
        , request
        , longModal
        , credentialParser
        , folderParser
        , templateLoader
        , temporaryStorage
        , templateParser
        , stringLoader
        , dateTimeService
        , confirmation
        , shareService
        , axios
        , arrayListService
        , folderListener
        , credentialListener
        , breadCrumbService
    ) {
        this.routes = routes;
        this.request = request;
        this.credentialParser = credentialParser;
        this.folderParser = folderParser;
        this.appStorage = appStorage;
        this.templateLoader = templateLoader;
        this.temporaryStorage = temporaryStorage;
        this.templateParser = templateParser;
        this.stringLoader = stringLoader;
        this.dateTimeService = dateTimeService;
        this.spinner = $("#pwm__node__container__spinner");
        this.tableBody = $("#pwm__node__container #table__body");
        this.confirmation = confirmation;
        this.shareService = shareService;
        this.axios = axios;
        this.arrayListService = arrayListService
        this.folderListener = folderListener;
        this.credentialListener = credentialListener;
        this.breadCrumbService = breadCrumbService;
        this.longModal = longModal;
    }

    determineNodeType(id) {
        return Util.isSet(id) ? id : NODE_ID_ROOT;
    }

    async loadDetails(id = NODE_ID_ROOT) {
        const _this = this;
        let rootId = _this.determineNodeType(id);
        _this.tableBody.html("");
        // _this.breadCrumbService.clear();

        _this.temporaryStorage.set(
            STORAGE_ID_ROOT
            , rootId
        );

        const templates = await glob.sync(__dirname + '/../../../../template/frontend/*.twig')
        const strings = require(__dirname + '/../../../../string/frontend/strings.json').password_manager;

        _this.credentialListener.parseNotClickedNode(
            templates
            , strings
        );

        _this.request.get(
            _this.routes.getNode(rootId)
            , {}
            , (x, y, z) => {
                const object = (x);

                if (RESPONSE_CODE_OK in object) {
                    const node = object[RESPONSE_CODE_OK]['messages']['node'];
                    const breadCrumb = object[RESPONSE_CODE_OK]['messages']['breadCrumb'];

                    _this.breadCrumbService.parse(
                        breadCrumb
                        , (id) => {
                            _this.loadDetails(id);
                        }
                    );

                    _this.parseTemplate(
                        node
                        , id
                        , templates
                        , strings
                    );

                    _this.spinner.removeClass("d-flex");
                    _this.spinner.addClass("d-none");

                }

            }
            , (x, y, z) => {
                console.log(x)
            }
            , () => {
                _this.spinner.addClass("d-flex");
                _this.spinner.removeClass("d-none");
            }
        );


    }

    parseTemplate(
        node
        , parentId
        , templates
        , strings
    ) {
        const _this = this;
        const length = node.edge_size;
        let parsed;

        _this.tableBody.children().remove();

        if (0 === length) {
            parsed = _this.parseEmptyNode(templates, strings);
            _this.tableBody.append(parsed);
            return;
        }

        parsed = _this.parseNodes(
            node
            , templates
            , strings
        );

        _this.tableBody.append(
            parsed
        );

        _this.spinner.addClass("d-none");
        _this.spinner.removeClass("d-flex");

    }

    parseEmptyNode(
        templates
        , strings
    ) {

        return this.templateParser.parse(
            templates["no-entries"]
            , {
                noPasswordsAvailable: strings["strings"]["credential"]["noPasswordsAvailable"]
                , noPasswordsAvailableDescription: strings["strings"]["credential"]["noPasswordsAvailable"]
            }
        );

    }

    parseNodes(
        node
        , templates
        , strings
    ) {
        const _this = this;
        const elements = [];


        for (let i in node.edges.content) {
            const edge = node.edges.content[i];

            if (edge === undefined || edge === null) {
                continue;
            }

            const n = edge.node || [];
            const type = edge.type;
            const isShared = type === NODE_TYPE_SHARE;
            const nodeType = n.type;

            if ([] === n) {
                continue;
            }
            let info = null;
            let tooltip = null;

            switch (nodeType) {
                case NODE_TYPE_CREDENTIAL:
                    info = _this.credentialParser.parse(n);
                    break;
                case NODE_TYPE_FOLDER:
                    info = _this.folderParser.parse(n, _this.strings);
                    break;
            }

            if (true === isShared) {
                tooltip = this.templateParser.parse(
                    strings["strings"]["credential"]['shared_with_you_by']
                    , {
                        "sharer": edge.owner.name
                    }
                );
            }

            const parsed = this.templateParser.parse(
                templates["credential-template"]
                , {
                    id: n.id
                    , type: n.type
                    , name: n.name
                    , info: info
                    , isShared: isShared
                    , tooltip: tooltip
                    , createTsDescription: _this.dateTimeService.format(n.create_ts.date)
                }
            );

            const singleElement = $(parsed);

            switch (nodeType) {
                case NODE_TYPE_CREDENTIAL:
                    _this.credentialListener.listen(singleElement);
                    break;
                case NODE_TYPE_FOLDER:
                    _this.folderListener.listen(singleElement, _this);
                    break;
            }

            const cm = new ContextMenu();
            const onClose = () => {
                const element = singleElement.find(".context-menu-menu-badge");
                element.off("click");
                element.off("one");
                cm.registerContextMenu(element, config, onClose);
            };
            const config = {
                data: [
                    {
                        id: "add__to__organization",
                        name: "Organization"
                    }
                ],
                menuSelected: function (id, value) {
                    console.log(value)
                    const addToOrga = new AddToOrganization(
                        _this.longModal
                        , _this.axios
                        , _this.routes
                        , templates
                        , _this.templateParser
                    );
                    addToOrga.init(id, n);
                }
            };
            cm.registerContextMenu(singleElement.find(".context-menu-menu-badge"), config, onClose);

            elements.push(singleElement);
        }


        return elements;
    }

}
