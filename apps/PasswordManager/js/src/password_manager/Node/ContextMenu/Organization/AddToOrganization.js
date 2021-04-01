import {RESPONSE_CODE_OK, RESPONSE_FIELD_MESSAGES} from "../../../../../../../../lib/js/src/Backend/Axios";

export class AddToOrganization {

    /**
     *
     * @param {Long} longModal
     * @param {Axios} axios
     * @param {Routes} routes
     * @param {Array} templates
     * @param {Parser} templateParser
     */
    constructor(
        longModal
        , axios
        , routes
        , templates
        , templateParser
    ) {
        this.longModal = longModal;
        this.axios = axios;
        this.routes = routes;
        this.templates = templates;
        this.templateParser = templateParser;
    }

    init(id, node) {

        const _this = this;
        const orgaSpinner = $("#spinner--orga");
        this.longModal.show(
            "header"
            , "Ok"
            , "Ok"
            , '<div class="spinner-border" id="spinner--orga" role="status"><span class="sr-only">Loading...</span></div>'
            , (modal) => {

                if (null !== node.organization) {
                    orgaSpinner.remove();
                    modal.find(".modal-body")
                        .append('The node belongs already to an organization!');
                    return;
                }

                this.axios.request(
                    this.routes.getOrganizations()
                ).then(
                    (r) => {
                        return r.data;
                    }
                ).then(
                    (data) => {
                        if (RESPONSE_CODE_OK in data) {
                            return data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES]['organizations']['content'];
                        }
                        return [];
                    }
                ).then(
                    (organizations) => {
                        orgaSpinner.hide();
                        modal.find(".modal-body")
                            .append(
                                _this.templateParser.parse(
                                    _this.templates['organizations']
                                    , {
                                        organizations: organizations
                                    }
                                )
                            );
                    }
                )
            }
        ).then((modalBody) => {
            if (null !== node.organization) {
                return;
            }
            const organizationId = $(modalBody).find("#organization--submit")
                .find('#organization--select option:selected').val();

            orgaSpinner.show();
            _this.axios.post(
                _this.routes.getOrganizationsAddNode()
                , {
                    node_id: node.id
                    , organization_id: organizationId
                }
            ).then((e) => {
                orgaSpinner.hide();
                console.log(e)
            }).catch((e) => {
                console.log(e)
                orgaSpinner.hide();
            })
        });

    }

}
