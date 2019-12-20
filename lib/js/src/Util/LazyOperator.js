export class LazyOperator {

    doAfterElementDisplayed(selector, time, method) {
        const _this = this;

        if (document.querySelector(selector) != null) {
            method(selector);
            return true;
        } else {
            // console.log("actually not available, checking again in " + time + " seconds");
            setTimeout(function () {
                _this.doAfterElementDisplayed(selector, time, method);
            }, time);
        }
    }

}