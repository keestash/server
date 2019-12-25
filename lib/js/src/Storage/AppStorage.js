export class AppStorage {

    storeToken(value) {
        localStorage.setItem('api_token', value);
    };

    storeUserHash(value) {
        localStorage.setItem('user_hash', value);
    };

    storeAPICredentials(token, userHash) {
        this.storeToken(token);
        this.storeUserHash(userHash);
    };

    getToken() {
        return localStorage.getItem('api_token');
    };

    getUserHash() {
        return localStorage.getItem('user_hash');
    };

    logCredentials() {
        console.log(
            this.getToken() + ' ' + this.getUserHash()
        )
    };

    validToken() {
        return "" !== this.getToken() && null !== this.getToken();
    };


    validUserHash() {
        return "" !== this.getUserHash() && null !== this.getUserHash();
    };


    isValid() {
        return this.validToken() && this.validUserHash();
    };

    deleteToken() {
        this.storeToken(null);
    };

    deleteUserHash() {
        this.storeUserHash(null);
    };

    clearAPICredentials() {
        this.deleteToken();
        this.deleteUserHash();
    }


}