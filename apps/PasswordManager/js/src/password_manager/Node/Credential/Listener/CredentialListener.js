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
import {Password} from "./Detail/Password";
import $ from "jquery";
import {NODE_TYPE_CREDENTIAL} from "../../Node";
import {Tab} from "../Tab/Tab";
import {Helper} from "../Tab/Helper/Helper";
import moment from "moment";
import {RESPONSE_CODE_OK} from "../../../../../../../../lib/js/src/Backend/Request";
import {PasswordUrl} from "./Detail/PasswordUrl";

export class CredentialListener {

    constructor(
        request
        , routes
        , appStorage
        , longModal
        , templateLoader
        , stringLoader
        , templateParser
        , confirmation
        , shareService
        , axios
        , arrayListService
        , urlService
    ) {
        this.request = request;
        this.routes = routes;
        this.appStorage = appStorage;
        this.helper = new Helper();
        this.longModal = longModal;
        this.templateLoader = templateLoader;
        this.stringLoader = stringLoader;
        this.templateParser = templateParser;
        this.nodeDetail = $("#pwm__node__detail");
        this.confirmation = confirmation;
        this.clickedNode = null;
        this.shareService = shareService;
        this.axios = axios;
        this.arrayListService = arrayListService;
        this.urlService = urlService;
    }

    listen(element) {
        const _this = this;
        Keestash.Main.readAssets()
            .then((assets) => {
                const templates = assets[0];
                const strings = assets[1];

                let passwordManager = strings["password_manager"];

                _this.listenElementClick(
                    element
                    , templates
                    , passwordManager.strings
                );
            })
    }

    listenElementClick(
        element,
        templates,
        strings
    ) {
        const _this = this;
        const type = element.attr("data-type");
        const id = element.attr("data-id");

        element.off("click").off("one").one(
            "click",
            () => {

                if (type !== NODE_TYPE_CREDENTIAL) return;
                if (_this.clickedNode === id) return;

                _this.clickedNode = id;

                _this.request.get(
                    _this.routes.getNode(id)
                    , {}
                    , (x, y, z) => {
                        let object = x;
                        const node = object[RESPONSE_CODE_OK]['messages']['node'];

                        _this.onTemplateLoad(
                            templates["detail-template"]
                            , strings["credential"]["detail"]
                            , node
                        );

                        _this.initObjects();
                        _this.initNodeAvatarListener();
                        _this.initTabListener(node, strings, templates);
                        // _this.initFormListener();
                    },
                    (x, y, z) => {
                        console.log(x)
                        console.log(y)
                        console.log(z)
                    }
                )
            });

    }

    // initFormListener() {
    //     $("#tab__detail__wrapper").on('keyup change paste', 'input, select, textarea', function () {
    //         console.log('Form changed!');
    //     });
    // }

    onTemplateLoad(template, passwordDetail, node) {
        const parsed = this.templateParser.parse(
            template
            , {
                // labels
                userNameLabel: passwordDetail.userNameLabel
                , passwordLabel: passwordDetail.passwordLabel
                , websiteLabel: passwordDetail.websiteLabel
                , activityLabel: passwordDetail.activityLabel
                , commentLabel: passwordDetail.commentLabel
                , commentHeader: passwordDetail.commentHeader
                , sharesLabel: passwordDetail.sharesLabel
                , pwGeneratorLabel: passwordDetail.pwGeneratorLabel
                , attachmentsLabel: passwordDetail.attachmentsLabel
                , qualityLabel: passwordDetail.qualityLabel
                , entropyLabel: passwordDetail.entropyLabel
                , upperCaseLabel: passwordDetail.upperCaseLabel
                , lowerCaseLabel: passwordDetail.lowerCaseLabel
                , digitLabel: passwordDetail.digitLabel
                , specialCharacterLabel: passwordDetail.specialCharacterLabel
                , notesLabel: passwordDetail.notesLabel
                , createdAt: passwordDetail.createdAt
                , updatedAt: passwordDetail.updatedAt

                // placeholder
                , passwordGeneratorPlaceholder: passwordDetail.passwordGeneratorPlaceholder

                // values
                , node: node
                , sharingEnabled: false === node.is_shared_to_me
                , sharedPublicly: null !== node.public_share && false === node.public_share.is_expired
                , createDate: moment(node.create_ts.date).format("YYYY-MM-DD HH:mm:ss")
                , updateDate: moment(node.create_ts.date).format("YYYY-MM-DD HH:mm:ss") // TODO add update ts
                , defaultValue: passwordDetail.defaultValue

                // strings
                , sharePublicly: passwordDetail.sharePublicly
                , publicShare: passwordDetail.publicShare
                , characterTypes: passwordDetail.characterTypes
                , minPasswordCharacters: passwordDetail.minPasswordCharacters
                , maxPasswordCharacters: passwordDetail.maxPasswordCharacters
                , stepPasswordCharacters: passwordDetail.stepPasswordCharacters
                , usePassword: passwordDetail.usePassword
                , save: passwordDetail.save
                , addComment: passwordDetail.addComment

            });

        this.nodeDetail.html("");
        this.nodeDetail.html(parsed);
    }

    initObjects() {

        const objects = [
            new Password(
                this.request
                , this.routes
            ),
            new PasswordUrl(this.urlService)
        ];

        for (let i = 0; i < objects.length; i++) {
            const o = objects[i];
            o.init();
        }
    }

    initNodeAvatarListener() {
        const nodeAvatar = $(".node-avatar");

        const _this = this;
        nodeAvatar.one(
            "click",
            () => {
                return;

                const myFile = $("input[id='my_file']");
                myFile.click();

                myFile.on(
                    "change",
                    (event) => {
                        const file = event.target.files[0];
                        const nodeId = myFile.data("node-id");

                        _this.axios.post(
                            _this.routes.getPasswordManagerNodeUpdateAvatar()
                            , {
                                nodeAvatar: file
                                , nodeId: nodeId
                            }
                        )
                            .then((response) => {

                                if (RESPONSE_CODE_OK in response.data) {
                                    nodeAvatar.attr("src", response.data[RESPONSE_CODE_OK].messages.base64);
                                }

                            })
                        ;
                        myFile.val('');
                        $("#my_form")[0].reset();
                    }
                )
            }
        )
    }

    initTabListener(node, strings, templates) {
        const _this = this;

        $('#detail__tabs a').one(
            "click",
            (e) => {

                const target = $(e.target);

                const tabName = target.data("tabname");

                const tab = new Tab();
                tab.init(tabName, node, strings, templates);
            });

        _this.helper.getFirstTab().trigger("click");
        _this.helper.attachActiveClass();

    }

    parseNotClickedNode(templates, strings) {
        this.clickedNode = null;
        this.nodeDetail.html(
            this.templateParser.parse(
                templates["no-entries"]
                , {
                    noPasswordsAvailable: strings["strings"]["credential"]["noPasswordsAvailable"]
                    , noPasswordsAvailableDescription: strings["strings"]["credential"]["noPasswordsAvailable"]
                }
            )
        )
    }

}
