import $ from "jquery";
import {RESPONSE_CODE_NOT_OK, RESPONSE_CODE_OK} from "../../../../../lib/js/src/UI/ModalHandler";

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
        this.buttonClicked = false;
    }

    async run() {
        const form = $("#forgot_password_form");
        const submitButton = $("#fp__rest");
        const input = $("#fp__input");
        const _this = this;

        form.submit(
            (event) => {
                event.preventDefault();

                if (true === _this.buttonClicked) return;
                _this.buttonClicked = true;
                _this.buttonService.disable(
                    submitButton
                    , true
                );

                if ("" === input.val().trim()) {
                    _this.inputService.invalid(input);
                    _this.buttonService.disable(
                        submitButton
                        , false
                    );
                    _this.buttonClicked = false;
                    return;
                }

                const values = {
                    input: input.val().trim()
                };

                _this.request.post(
                    _this.routes.getForgotPasswordSubmit()
                    , values
                    , (response, status, xhr) => {
                        const object = JSON.parse(response);
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
                            , 'new event'
                        );

                        _this.buttonService.disable(
                            submitButton
                            , false
                        );
                        _this.buttonClicked = false;

                    }
                    , (response, status, xhr) => {
                        _this.miniModal.show(
                            'password reset'
                            , 'ok'
                            , 'close'
                            , "There was an error. Please try again or contact our support"
                            , 'new event'
                        );

                        _this.buttonService.disable(
                            submitButton
                            , false
                        );
                        _this.buttonClicked = false;
                    }
                );
            }
        );
    }

}