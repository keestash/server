import Formula from "../../../../lib/js/src/Formula";
import {Routes} from "./Routes";
import {RESPONSE_CODE_OK} from "../../../../lib/js/src/UI/ModalHandler";
import Router from "../../../../lib/js/src/Router";

export class Main {
    handle() {
        $("#ii__main__button").click(function (e) {
            e.preventDefault();

            const formula = new Formula();
            const routes = new Routes();

            window.setTimeout(
                function () {
                    formula.post(
                        routes.getInstallInstanceEndUpdate()
                        , {}
                        , function (x, y, z) {
                            const object = JSON.parse(x);

                            if (RESPONSE_CODE_OK in object) {
                                const router = new Router();
                                const routeTo = object[RESPONSE_CODE_OK]['messages']['route_to'];
                                router.route(routeTo);
                            }
                        }
                        , function (x, y, z) {
                            console.log(x);
                        }
                    );
                }, 500
            );
        })
    }
}