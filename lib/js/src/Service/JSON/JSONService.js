export class JSONService {

    parse(value) {
        try {
            return JSON.parse(value);
        } catch (e) {
            return {};
        }
    }

    isJson(value) {
        if (typeof value !== 'string')
            value = JSON.stringify(value);

        try {
            JSON.parse(value);
            return true;
        } catch (e) {
            return false;
        }
    }

}