import {Util} from "../../Util/Util";
import {RESPONSE_CODE_OK} from "../../UI/ModalHandler";
import {TemplateStorage} from "./TemplateStorage";

export class TemplateLoader {

    constructor(
        request
        , globalRoutes
    ) {
        this.request = request;
        this.globalRoutes = globalRoutes;
    }

    async load(clearFirst = false) {

        const _this = this;

        _this.request.get(
            _this.globalRoutes.getAllTemplates()
            , {}
            , (x, y, z) => {
                const isJson = Util.isJson(x);
                if (true === isJson) {
                    const object = JSON.parse(x);

                    if (RESPONSE_CODE_OK in object) {
                        const templateStorage = new TemplateStorage();
                        const templates = object[RESPONSE_CODE_OK]["messages"]["templates"];

                        if (true === clearFirst) {
                            templateStorage.clear()
                                .then(() => {
                                    templateStorage.addAll(templates);
                                })
                        } else {
                            templateStorage.addAll(templates);
                        }

                    }
                }

            }
        );
    }
}