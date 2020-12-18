import * as moment from "moment";

export class DateTimeService {

    constructor(appStorage) {
        moment.locale(appStorage.getLocale())
    }

    format(dateTime) {
        return moment(dateTime).format("LT")
    }
}
