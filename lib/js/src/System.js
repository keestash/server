export default function System() {

    this.throttle = function (func, delay) {
        let timer = null;
        return function () {
            if (!timer) {
                timer = setTimeout(function () {
                    timer = null;
                    func();
                }, delay);
            }
        }
    }

    this.copyToClipboard = function (text) {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(text).select();
        document.execCommand("copy");
        $temp.remove();
    }

}