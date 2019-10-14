import {ConfigFile} from "./ConfigFile";
import {DirsWritable} from "./DirsWritable";
import {Main} from "./Main";

(function () {
    if (!Keestash.Apps.InstallInstance) {
        Keestash.Apps.InstallInstance = {};
    }

    Keestash.Apps.InstallInstance = {

        init: function () {

            const handler = [
                new ConfigFile()
                , new DirsWritable()
                , new Main()
            ];

            for (let i = 0; i < handler.length; i++) {
                handler[i].handle();
            }


        }


    }

})
();

$(document).ready(function () {
    Keestash.Apps.InstallInstance.init();
});

