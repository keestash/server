import {RESPONSE_CODE_OK} from "../../../../../lib/js/src/UI/ModalHandler";
import Router from "../../../../../lib/js/src/Router";

export class EndUpdate {

    constructor(formula, routes) {
        this.allEvents = {
            "config_data": false
            , "writable_dirs": false
            , "has_data_dirs": false
        };
        this.button = $("#ii__main__button");
        this.formula = formula;
        this.routes = routes;
    }

    handle() {

        const _this = this;
        $(document).on("config_data", function () {
            _this.allEvents["config_data"] = true;
            _this.checkButtonState();
        });

        $(document).on("writable_dirs", function () {
            _this.allEvents["writable_dirs"] = true;
            _this.checkButtonState();
        });

        $(document).on("has_data_dirs", function () {
            _this.allEvents["has_data_dirs"] = true;
            _this.checkButtonState();
        });

        _this.listenToButton();
    }

    checkButtonState() {
        const _this = this;
        let allSet = true;

        $.each(_this.allEvents, function (i, v) {
            allSet = allSet && v;
        });

        if (true === allSet) {
            _this.button.removeClass("disabled");
        }
    }

    listenToButton() {
        const _this = this;
        _this.button.click(function (e) {
            e.preventDefault();
            console.log("ending");

            window.setTimeout(
                function () {
                    _this.formula.post(
                        _this.routes.getInstallInstanceEndUpdate()
                        , {}
                        , function (x, y, z) {
                            const object = JSON.parse(x);
                            console.log(object);

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