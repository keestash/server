import {Loader} from "../IDBStorage/Loader";

const IDB_DB_NAME = "templatestore";

export class TemplateLoader extends Loader {

    constructor(
        request
        , globalRoutes
    ) {
        super(
            request
            , globalRoutes.getAllTemplates()
            , IDB_DB_NAME
        )
    }

}