import {Loader} from "../IDBStorage/Loader";

const IDB_DB_NAME = "ztringstore";

export class StringLoader extends Loader {

    constructor(
        request
        , globalRoutes
    ) {
        super(
            request
            , globalRoutes.getAllStrings()
            , IDB_DB_NAME
        )
    }

}