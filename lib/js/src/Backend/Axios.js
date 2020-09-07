import axios from 'axios/index'

export const RESPONSE_CODE_OK = 1000;
export const RESPONSE_CODE_NOT_OK = 2000;
export const RESPONSE_CODE_SESSION_EXPIRED = 3000;

export class Axios {

    constructor(appStorage) {
        this.appStorage = appStorage;
    }

    request(url, data = {}) {
        data = this.prepareData(data);
        return axios.get(
            url
            , {
                params: data
            }
        )
    }

    post(url, data = {}) {
        data = this.prepareData(data);

        const formData = new FormData();


        for (let key in data) {
            formData.append(key, data[key])
        }
        return axios({
            method: 'post',
            url: url,
            data: formData
        });
    }

    prepareData(data) {
        data.token = this.appStorage.getToken();
        data.user_hash = this.appStorage.getUserHash();
        return data;
    }
}
