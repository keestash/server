import {RESPONSE_CODE_OK} from "../../../../../lib/js/src/Backend/Request";

export class Page {

    constructor(
        request
        , routes
        , router
    ) {
        this.request = request;
        this.routes = routes;
        this.router = router;
    }

    run() {
        const _this = this;
        const button = $("#i__end__update");
        button.removeClass("disabled");

        button.off("click").click(() => {

            _this.request.post(
                _this.routes.getInstallAppsAll()
                , {}
                , (x, y, z) => {
                    const object = x;
                    console.log(object);
                    if (RESPONSE_CODE_OK in object) {
                        const result_object = object[RESPONSE_CODE_OK]["messages"];
                        let routeTo = result_object['routeTo'];

                        console.log("installed all apps. Going to redirect...");
                        console.log("to " + routeTo);
                        console.log("to " + routeTo);

                        setTimeout(() => {
                            _this.router.routeTo(routeTo);
                        }, 3000);


                    } else {
                        alert("there was an error :(")
                    }
                }
                , (x, y, z) => {
                    console.log(x)
                }
            )
        });
    }

}
