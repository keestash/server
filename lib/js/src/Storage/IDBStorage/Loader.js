import {Util} from "../../Util/Util";
import {RESPONSE_CODE_OK} from "../../UI/ModalHandler";
import {IDBStorage} from "../IDBStorage/IDBStorage";
import {JSONService} from "../../Service/JSON/JSONService";

export class Loader {

    constructor(
        request
        , route
        , dbName
    ) {
        this.request = request;
        this.route = route;
        this.storage = new IDBStorage(dbName);
        this.jsonService = new JSONService();
    }

    async load(clearFirst = false) {

        const _this = this;

        _this.request.get(
            _this.route
            , {}
            , async (x, y, z) => {

                const isJson = Util.isJson(x);

                if (true === isJson) {
                    const object = JSON.parse(x);

                    if (RESPONSE_CODE_OK in object) {
                        const data = object[RESPONSE_CODE_OK]["messages"]["data"];

                        if (true === clearFirst) {
                            await _this.storage.clear()
                        }

                        await _this.storage.addAll(data);

                    }

                }

            }
        );
    }

    async read() {
        return await this.storage.getAll();
    }

}