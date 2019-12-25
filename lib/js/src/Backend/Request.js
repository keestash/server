import {RESPONSE_CODE_NOT_OK, RESPONSE_CODE_OK, RESPONSE_CODE_SESSION_EXPIRED} from "../UI/ModalHandler";
import $ from "jquery";
import modal from "../UI/modal";
import FormData from "form-data";

export const POST = "post";
export const GET = "get";

const DEFAULT_WAIT_TIME_BEFORE_REDIRECT_TO_LOGIN = 5000;

export class Request {

    constructor(
        consoleLogger
        , appStorage
        , router
    ) {
        this.consoleLogger = consoleLogger;
        this.appStorage = appStorage;
        this.router = router;
    }

    request(route, type, data = {}, successCB, errorCB) {

        data['token'] = this.appStorage.getToken();
        data['user_hash'] = this.appStorage.getUserHash();

        $.ajax({
            url: route,
            type: type,
            cache: false,
            data: data,
            success: function (response, status, xhr) {

                const object = JSON.parse(response);

                if (RESPONSE_CODE_SESSION_EXPIRED in object) {
                    return false;
                }

                successCB(response, status, xhr);
            },
            error: function (response, status, xhr) {
                errorCB(response, status, xhr);
            }
        });
    };

    post(route, data, successCB, errorCB) {
        if (typeof successCB === "undefined") {
            successCB = this.success;
        }
        if (typeof errorCB === "undefined") {
            errorCB = this.error;
        }

        this.request(
            route
            , POST
            , data
            , successCB
            , errorCB
        );
    };

    get(route, data, successCB, errorCB) {

        this.request(
            route
            , GET
            , data
            , successCB
            , errorCB
        );

    };

    handleTokenExpired(jsonObject, route) {
        this.clearCredentials();
        this.notifyExpiry(jsonObject, route);
        this.redirectToLogin();
    };

    notifyExpiry(jsonObject, route) {
        modal.miniModal(jsonObject[RESPONSE_CODE_SESSION_EXPIRED]['message'] + " for route " + route);
    };

    success(x, y, z) {
        x = JSON.parse(x);
        modal.miniModal(x[RESPONSE_CODE_OK]["message"]);
    };

    error(x, y, z) {
        x = JSON.parse(x);
        modal.miniModal(x[RESPONSE_CODE_NOT_OK]["message"]);
    };

    clearCredentials() {
        this.appStorage.clearAPICredentials();
    };

    redirectToLogin() {
        const _this = this;
        window.setTimeout(
            function () {
                _this.router.routeTo("/login");
            }, DEFAULT_WAIT_TIME_BEFORE_REDIRECT_TO_LOGIN
        );
    };
}