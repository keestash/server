import Formula from "../../../../lib/js/src/Formula";
import {Routes} from "./Routes";
import {RESPONSE_CODE_OK} from "../../../../lib/js/src/UI/ModalHandler";
import modal from "../../../../lib/js/src/UI/modal";

export class DirsWritable {
    handle() {

        $("#dirs__writable").submit(function (e) {
            e.preventDefault();
            const routes = new Routes();
            const formula = new Formula();
            formula.post(
                routes.getInstallInstanceDirsWritable()
                , []
                , function (x, y, z) {
                    const object = JSON.parse(x);

                    if (RESPONSE_CODE_OK in object) {
                        $("#dirs__writable__part").fadeOut(1000, function () {
                            $(this).remove();
                        });
                    } else {
                        modal.miniModal("The dirs are still not readable/writable. Please try again!");
                    }
                }
                , function (x, y, z) {
                    console.log(x)
                }
            )
        });

    }
}