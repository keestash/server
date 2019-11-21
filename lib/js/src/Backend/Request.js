import {RESPONSE_CODE_NOT_OK, RESPONSE_CODE_OK, RESPONSE_CODE_SESSION_EXPIRED} from "../UI/ModalHandler";

const RESPONSE_FIELD_MESSAGES = "messages";
const RESPONSE_FIELD_STATUS = "status";

export class Request {

    constructor(consoleLogger) {
        this.consoleLogger = consoleLogger;
    }

    request(successCB) {
        const _this = this;

        $.ajax(
            {
                url: ""
                , type: ""
                , cache: ""
                , data: ""
                , success: function (response, status, xhr) {
                    const object = JSON.parse(response);

                    if (RESPONSE_CODE_OK in object) {
                        const messages = object[RESPONSE_FIELD_MESSAGES];
                        const status = object[RESPONSE_FIELD_STATUS];
                        successCB(messages, status);
                    } else if (RESPONSE_CODE_NOT_OK in object) {

                    } else if (RESPONSE_CODE_SESSION_EXPIRED in object) {
                        _this.handleTokenExpired();
                    } else {
                        _this.consoleLogger.debug("unknown response code");
                    }
                }
            }
        )

    }

    handleTokenExpired() {
        this.clearCredentials();
        this.notifyExpiry();
        this.redirectToLogin();
    }
}