import '../semantic/semantic.min';
import modal from "./modal";

export const RESPONSE_CODE_OK = 1000;
export const RESPONSE_CODE_NOT_OK = 2000;
export const RESPONSE_CODE_SESSION_EXPIRED = 3000;

export default {

    handleSuccess: function (response) {
        let object = JSON.parse(response);
        let message = null;
        let success = false;

        if (RESPONSE_CODE_OK in object) {
            message = object[RESPONSE_CODE_OK]["message"];
            success = true;
        } else {
            message = object[RESPONSE_CODE_NOT_OK]["message"];
        }

        modal.miniModal(message);
        return success;
    }
    , handleError(response) {
        modal.miniModal("Error " + response);
    }
}