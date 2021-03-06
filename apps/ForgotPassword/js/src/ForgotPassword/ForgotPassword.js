import $ from "jquery";
import {RESPONSE_CODE_NOT_OK, RESPONSE_CODE_OK} from "../../../../../lib/js/src/Backend/Request";

export class ForgotPassword {

    constructor(
        buttonService
        , inputService
        , request
        , routes
        , miniModal
    ) {
        this.buttonService = buttonService;
        this.inputService = inputService;
        this.request = request;
        this.routes = routes;
        this.miniModal = miniModal;
    }

    async run() {
        const form = $("#forgot_password_form");
        const submitButton = $("#fp__rest");
        const input = $("#fp__input");
        const _this = this;

        form.submit(
            (event) => {
                event.preventDefault();

                _this.disableForm(submitButton, true);

                if ("" === input.val().trim()) {
                    _this.inputService.invalid(input);
                    _this.disableForm(submitButton, false);
                    return;
                }

                const values = {
                    input: input.val().trim()
                };

                _this.request.post(
                    _this.routes.getForgotPasswordSubmit()
                    , values
                    , (response, status, xhr) => {
                        const object = response;
                        let result = null;

                        if (RESPONSE_CODE_OK in object) {
                            result = object[RESPONSE_CODE_OK];
                        } else if (RESPONSE_CODE_NOT_OK in object) {
                            result = object[RESPONSE_CODE_NOT_OK];
                        }

                        _this.miniModal.show(
                            result['header']
                            , 'ok'
                            , 'close'
                            , result['message']
                        );

                        _this.disableForm(submitButton, false);
                        input.val("");
                    }
                    , (response, status, xhr) => {
                        _this.miniModal.show(
                            'password reset'
                            , 'ok'
                            , 'close'
                            , "There was an error. Please try again or contact our support"
                        );

                        _this.disableForm(submitButton, false);

                    }
                );
            }
        );
    }

    disableForm(button, disable) {
        this.buttonService.disable(
            button
            , disable
        );
    }
}
