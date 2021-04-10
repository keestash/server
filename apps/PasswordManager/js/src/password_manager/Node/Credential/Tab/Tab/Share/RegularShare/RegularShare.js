import {RESPONSE_CODE_OK} from "../../../../../../../../../../../lib/js/src/Backend/Request";
import $ from "jquery";

export class RegularShare {

    constructor(
        axios
        , routes
        , arrayListService
        , templateParser
        , shareService
        , appStorage
        , miniModal
        , request
    ) {
        this.axios = axios;
        this.routes = routes;
        this.arrayListService = arrayListService;
        this.templateParser = templateParser;
        this.shareService = shareService;
        this.appStorage = appStorage;
        this.miniModal = miniModal;
        this.request = request;
    }

    init(node, strings, templates) {
        this.initShareableUsers(node, strings, templates);
        this.initSharedUsers(node, strings, templates);
    }

    initShareableUsers(node, strings, templates) {
        const _this = this;
        this.axios.request(
            this.routes.getShareableUsers(node.id)
        )
            .then((response) => {

                const data = response.data;
                let results = [];

                if (!(RESPONSE_CODE_OK in data)) {
                    return;
                }

                const content = data[RESPONSE_CODE_OK]['messages']['user_list']['content'];
                const pictures = data[RESPONSE_CODE_OK]['messages']['pictures'];

                $.each(
                    content
                    , (index, element) => {
                        if (null === element) {
                            return true; // continue
                        }

                        if (node.user_id === element.id) {
                            return true; //continue
                        }

                        results.push(
                            {
                                "name": element.name
                                , "longName": element.first_name + " " + element.last_name
                                , "image": pictures[element.id]
                                , "id": element.id
                            }
                        );
                    });

                const searchBox = $("#searchFollowee .typeahead");

                searchBox.typeahead(
                    {
                        hint: true,
                        highLight: true,
                        minLength: 1,
                        classNames: {
                            input: "form-control form-control-sm prompt",
                            hint: "form-control form-control-sm prompt",
                            menu: "form-control form-control prompt",
                            open: "p-0 m-0"
                        }
                    }
                    , {
                        name: 'results',
                        display: (data) => {
                            searchBox.html('');
                        },
                        source: (query, syncResult, asyncResult) => {
                            const matches = [];
                            const substrRegex = new RegExp(query, 'i');

                            $.each(
                                results
                                , (i, data) => {
                                    if (substrRegex.test(data.name)) {
                                        matches.push(data);
                                        return; // means continue in regular programming languages
                                    }

                                    if (substrRegex.test(data.longName)) {
                                        matches.push(data);
                                        return; // means continue in regular programming languages
                                    }

                                    if (substrRegex.test(data.id)) {
                                        matches.push(data);
                                        return; // means continue in regular programming languages
                                    }
                                });
                            syncResult(matches);
                        },
                        templates: {
                            empty: () => {
                                return _this.templateParser.parse(
                                    templates["no-shareable-users"]
                                    , {
                                        noDataFound: strings["credential"]["sharePublicly"]["noUsersToShareFound"]
                                    }
                                )
                            },
                            suggestion: (data) => {
                                return _this.templateParser.parse(
                                    templates['share-suggestion']
                                    , {
                                        data: data
                                    }
                                )
                            }
                        }
                    }
                );

                searchBox.bind(
                    'typeahead:select'
                    , (event, suggestion) => {

                        _this.shareService.shareWith(
                            node.id
                            , suggestion.id
                            , _this.routes.getShare()
                        )
                            .then((response) => {

                                const data = response.data;

                                if (RESPONSE_CODE_OK in data) {
                                    const share = response.data[RESPONSE_CODE_OK]["messages"]["share"];
                                    const isShared = null !== share;

                                    if (false === isShared) {
                                        _this.showError();
                                        return
                                    }

                                    _this.addToUserList(
                                        {
                                            id: share.id
                                        }
                                        , {
                                            image: suggestion.image
                                            , name: suggestion.name
                                            , id: suggestion.id
                                        }
                                        , templates
                                    );

                                    this.initRemoveSharedUserListener();

                                } else {
                                    _this.showError();
                                }

                            })
                            .catch(
                                (response) => {
                                    console.log(response)
                                }
                            )
                    }
                );


            })
            .catch();
    }

    initSharedUsers(node, strings, templates) {
        const shareWrapper = $("#share__results");
        const _this = this;

        shareWrapper.find('ul').html('');
        shareWrapper.ready(
            () => {
                const sharedUsersCount = node.shared_to.length;

                if (0 === sharedUsersCount) {
                    shareWrapper.html(
                        _this.templateParser.parse(
                            templates['no-shared-users']
                            , {
                                noSharedUsers: strings["credential"]["sharePublicly"]["noSharedUsers"]
                            }
                        )
                    );
                    return;
                }

                const sharedTo = _this.arrayListService.excludeNullValues(node.shared_to.content);

                $.each(
                    sharedTo
                    , (i, dataset) => {
                        _this.addToUserList(
                            {
                                id: dataset.id
                            }
                            , {
                                name: dataset.user.name
                                , image: _this.routes.getUserProfilePicture(
                                    _this.appStorage.getToken()
                                    , _this.appStorage.getUserHash()
                                    , dataset.user.id
                                )
                                , id: dataset.user.id
                            }
                            , templates
                        )

                    }
                );

                this.initRemoveSharedUserListener();

            }
        )
    }

    addToUserList(share, user, templates) {

        const shareWrapper = $("#share__results");
        const parsed = this.templateParser.parse(
            templates['shared-user']
            , {
                user: user
                , share: share
            }
        );
        shareWrapper.find("ul").prepend(parsed);

    }

    showError() {
        console.log("error :(")
    }

    initRemoveSharedUserListener() {
        const _this = this;

        $("#share__results").find(".remove").off("click").off("one").one(
            "click",
            (e) => {
                const target = $(e.target);
                const shareId = target.data("share-id");
                _this.miniModal.show(
                    'Do you really wanna delete'
                    , 'Yes'
                    , 'No'
                    , 'This sharee is going to be deleted'
                ).then(
                    (modal) => {

                        _this.request.post(
                            _this.routes.getPasswordManagerShareeRemove()
                            , {
                                shareId: shareId
                            }
                            , (response) => {
                                const object = (response);

                                if (RESPONSE_CODE_OK in object) {

                                    const children = $("#share__results").find("ul").children();
                                    const respondedShareId = parseInt(object[RESPONSE_CODE_OK]["messages"]["shareId"]);

                                    $.each(
                                        children
                                        , (i, v) => {
                                            const li = $(v);
                                            const shareId = parseInt(li.data("share-id"));

                                            if (shareId === respondedShareId) {
                                                li.remove();
                                            }

                                        }
                                    )

                                    modal.modal('hide');
                                }
                            }
                            , (error) => {
                                console.log(error);
                            }
                        )
                    }
                )
            }
        )
    }
}
