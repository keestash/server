import {Config} from "./Installer/Config";
import {DirsWritable} from "./Installer/DirsWritable";
import Formula from "../../../../lib/js/src/Formula";
import {Routes} from "./Routes";
import {HasDataDirs} from "./Installer/HasDataDirs";
import {EndUpdate} from "./Installer/EndUpdate";
import {LazyOperator} from "../../../../lib/js/src/Util/LazyOperator";

(function () {
    if (!Keestash.Apps.InstallInstance) {
        Keestash.Apps.InstallInstance = {};
    }

    Keestash.Apps.InstallInstance = {

        init: function () {
            const formula = new Formula();
            const routes = new Routes();
            const lazyOperator = new LazyOperator();

            const handler = [

                new Config(
                    formula
                    , routes
                )

                , new DirsWritable(
                    formula
                    , routes
                    , lazyOperator
                )
                , new HasDataDirs(
                    formula
                    , routes
                    , lazyOperator
                )
                , new EndUpdate(
                    formula
                    , routes
                )
            ];

            for (let i = 0; i < handler.length; i++) {
                handler[i].handle();
            }


        }


    }

})();

$(document).ready(function () {
    Keestash.Apps.InstallInstance.init();
});

