import {Config} from "../Installer/Config";
import {
    LAZY_OPERATOR,
    MINI_MODAL,
    REQUEST,
    ROUTER,
    TEMPLATE_LOADER,
    TEMPLATE_PARSER
} from "../../../../../lib/js/src/StartUp";
import {DirsWritable} from "../Installer/DirsWritable";
import {HasDataDirs} from "../Installer/HasDataDirs";
import {EndUpdate} from "../Installer/EndUpdate";
import {Routes} from "../Routes";

export class Container {

    setUp(container){
        const routes = new Routes();

        const handler = [

            new Config(
                container.query(REQUEST)
                , container.query(TEMPLATE_LOADER)
                , container.query(TEMPLATE_PARSER)
                , container.query(MINI_MODAL)
                , routes
            )

            , new DirsWritable(
                container.query(REQUEST)
                , container.query(LAZY_OPERATOR)
                , container.query(TEMPLATE_LOADER)
                , container.query(TEMPLATE_PARSER)
                , routes
            )

            , new HasDataDirs(
                container.query(REQUEST)
                , container.query(LAZY_OPERATOR)
                , container.query(TEMPLATE_LOADER)
                , container.query(TEMPLATE_PARSER)
                , routes
            )

            , new EndUpdate(
                container.query(REQUEST)
                , container.query(ROUTER)
                , container.query(TEMPLATE_LOADER)
                , container.query(TEMPLATE_PARSER)
                , routes
            )

        ];


        for (let i = 0; i < handler.length; i++) {
            container.register(
                handler[i]
            );
        }
        return container;
    }
}