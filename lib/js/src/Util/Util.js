export class Util {
    isJson(value) {
        if (typeof value != 'string')
            value = JSON.stringify(value);

        try {
            JSON.parse(value);
            return true;
        } catch (e) {
            return false;
        }
    }
}