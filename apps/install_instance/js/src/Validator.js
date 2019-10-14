import Input from "../../../../lib/js/src/UI/Input";

export class Validator {
    isEmpty(val) {
        if (val === "") return true;
        if (val === null) return true;
        if (typeof val === 'undefined') return true;
        return false;
    }

    getValIfExists(name) {
        const element = $("#" + name);
        if (0 === element.length) return null;
        return element.val();
    }

    isValidSelect(val, id) {
        if (false === this.isEmpty(val)) return true;
        if (val === "enabled") return true;
        if (val === "disabled") return true;

        window.setTimeout(function () {
            Input.invalid("#" + id);
        }, 500);
        return false;
    }

    isValid(val, id) {

        if (this.isEmpty(val)) {

            window.setTimeout(function () {
                Input.invalid("#" + id);
            }, 500);

            return false;
        }

        return true;
    }
}