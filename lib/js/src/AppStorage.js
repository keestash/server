    export default function AppStorage() {

        this.storeToken = function (value) {
            localStorage.setItem('api_token', value);
        };

        this.storeUserHash = function (value) {
            localStorage.setItem('user_hash', value);
        };

        this.storeAPICredentials = function (token, userHash) {
            this.storeToken(token);
            this.storeUserHash(userHash);
        };

        this.getToken = function () {
            return localStorage.getItem('api_token');
        };

        this.getUserHash = function () {
            return localStorage.getItem('user_hash');
        };

        this.logCredentials = function () {
            console.log(
                this.getToken() + ' ' + this.getUserHash()
            )
        };

        this.validToken = function () {
            return "" !== this.getToken() && null !== this.getToken();
        };


        this.validUserHash = function () {
            return "" !== this.getUserHash() && null !== this.getUserHash();
        };


        this.isValid = function () {
            return this.validToken() && this.validUserHash();
        };

        this.deleteToken = function () {
            this.storeToken(null);
        };

        this.deleteUserHash = function () {
            this.storeUserHash(null);
        };

        this.clearAPICredentials = function () {
            this.deleteToken();
            this.deleteUserHash();
        }

    };

