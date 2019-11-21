import $ from 'jquery';
import AppStorage from "./AppStorage";
import Router from "./Router";
import {RESPONSE_CODE_SESSION_EXPIRED} from "./UI/ModalHandler";
import modal from "./UI/modal";
import routes from "./Backend/routes";

export default function Formula() {
    const POST = "post";
    const GET = "get";

    this.request = function (route, type, data, successCB, errorCB) {

        let appStorage = new AppStorage();
        const formula = new Formula();
        data['token'] = appStorage.getToken();
        data['user_hash'] = appStorage.getUserHash();

        $.ajax({
            url: route,
            type: type,
            cache: false,
            data: data,
            success: function (response, status, xhr) {

                const object = JSON.parse(response);

                if (RESPONSE_CODE_SESSION_EXPIRED in object) {
                    formula.handleTokenExpired(object, route);
                    return false;
                }

                successCB(response, status, xhr);
            },
            error: function (response, status, xhr) {
                errorCB(response, status, xhr);
            }
        });
    };

    this.post = function (route, data, successCB, errorCB) {
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

    this.get = function (route, data, successCB, errorCB) {

        this.request(
            route
            , GET
            , data
            , successCB
            , errorCB
        );

    };

    this.handleTokenExpired = function (jsonObject, route) {
        this.clearCredentials();
        this.notifyExpiry(jsonObject, route);
        this.redirectToLogin();
    };

    this.notifyExpiry = function (jsonObject, route) {
        modal.miniModal(jsonObject[RESPONSE_CODE_SESSION_EXPIRED]['message'] + " for route " + route);
    };

    this.success = function (x, y, z) {
        x = JSON.parse(x);
        modal.miniModal(x[1000]["message"]);
    };

    this.error = function (x, y, z) {
        x = JSON.parse(x);
        modal.miniModal(x[2000]["message"]);
    };

    this.clearCredentials = function () {
        let appStorage = new AppStorage();
        appStorage.clearAPICredentials();
    };

    this.redirectToLogin = function () {
        window.setTimeout(
            function () {
                let router = new Router();
                router.routeTo("/login");
            }, 5000
        );
    };
};