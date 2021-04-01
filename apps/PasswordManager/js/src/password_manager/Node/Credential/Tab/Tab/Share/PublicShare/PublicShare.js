import $ from "jquery";
import {Host} from "../../../../../../../../../../../lib/js/src/Backend/Host";
import {RESPONSE_CODE_OK} from "../../../../../../../../../../../lib/js/src/Backend/Axios";

export class PublicShare {

    constructor(
        confirmationModal
        , axios
        , routes
    ) {
        this.confirmationModal = confirmationModal;
        this.axios = axios;
        this.routes = routes;
    }

    init(node, strings) {
        this.initPublicSharing(node, strings);
        this.initShareButtonListener(node.public_share);
    }


    initPublicSharing(node, strings) {
        const _this = this;
        const isShared = null !== node.public_share && false === node.public_share.is_expired;

        $("#share_publicly").one(
            "click",
            (event) => {
                event.preventDefault();

                if (true === isShared) {
                    $("#share_publicly").prop('checked', false);
                    $("#ii__copy__link__button").addClass('ks__hidden');
                    console.log('still shared, please take the link');
                    return true;
                }

                _this.confirmationModal.show(
                    strings.credential.sharePublicly.passwordShareHeader
                    , strings.credential.sharePublicly.passwordSharePositiveButtonText
                    , strings.credential.sharePublicly.passwordShareNegativeButtonText
                    , strings.credential.sharePublicly.passwordShareQuestion
                )
                    .then(
                        (modal) => {
                            _this.axios.post(
                                _this.routes.getPasswordManagerSharePublicly()
                                , {
                                    node_id: node.id
                                }
                            ).then(
                                (response) => {
                                    const data = response.data;
                                    const copyLinkButton = $("#copy_link_button");

                                    if (RESPONSE_CODE_OK in data) {
                                        copyLinkButton.addClass("d-flex");
                                        copyLinkButton.addClass("flex-column");
                                        copyLinkButton.addClass("align-items-end");
                                        copyLinkButton.removeClass("copy__link__invisible");
                                        _this.initShareButtonListener(data[RESPONSE_CODE_OK].messages.share)
                                        _this.confirmationModal.close();
                                        return;
                                    }

                                    copyLinkButton.addClass("copy__link__invisible");
                                    copyLinkButton.removeClass("d-flex");
                                    copyLinkButton.removeClass("flex-column");
                                    copyLinkButton.removeClass("align-items-end");

                                }
                            )

                            ;
                        }
                    );

            });
    }

    initShareButtonListener(publicShare) {

        const _this = this;
        $("#ii__copy__link__button").ready(() => {
            $("#ii__copy__link__button")
                .off("click")
                .off("one")
                .one(
                    "click",
                    (e) => {
                        e.preventDefault();

                        const host = new Host();

                        if (null === publicShare) {
                            console.log("no public share. Exiting");
                            return;
                        }

                        const url = _this.routes.getPublicShareLink(publicShare.hash);
                        _this.copyToClipboard(url);

                        console.log("copied to clipboard");
                        console.log(url);
                    });
        });
    }

    copyToClipboard(text) {
        const input = $("<input>");
        $("body").append(input);
        input.val(text).select();
        document.execCommand("copy");
        input.remove();
    }
}
