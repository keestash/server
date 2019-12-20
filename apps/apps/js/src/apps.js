import Formula from "../../../../lib/js/src/Formula";
import routes from "../../../../lib/js/src/Backend/routes";

(function () {
    if (!Keestash.AppsApp) {
        Keestash.AppsApp = {};
    }

    Keestash.AppsApp = {

        listen: function () {
            $(".apps__app__checkbox").on("click", function () {
                const checked = $(this).prop("checked");
                const appId = $(this).attr("data-app-id");
                const formula = new Formula();

                formula.post(
                    routes.getAppsUpdate(appId)
                    , {
                        "activate": true === checked
                        , "app_id": appId
                    },
                    function (x) {
                        console.log(x)
                    }
                );

            });
        }

    }

})
();

$(document).ready(function () {
    Keestash.AppsApp.listen();
});

