import $ from 'jquery';

export default {
    invalidColor: '#CC7F7F',
    shake: function (elementName) {
        $(elementName).each(function () {
            let intShakes = 7;
            let intDistance = 7;
            let intDuration = 10;
            $(this).css("position", "relative");
            for (let x = 1; x <= intShakes; x++) {
                $(this).animate({left: (intDistance * -1)}, (((intDuration / intShakes) / 4)))
                    .animate({left: intDistance}, ((intDuration / intShakes) / 2))
                    .animate({left: 0}, (((intDuration / intShakes) / 4)));
            }
        });
        $(elementName).css("border-color", this.invalidColor);
    }
    , invalid: function (elementName) {
        $(elementName).css('border-color', this.invalidColor);
        this.shake(elementName);
        this.addRevertListener(elementName);
    }
    , addRevertListener: function (elementName) {
        $(elementName).on("input", function () {
            $(elementName).css('border-color', '');
            $(elementName).off("input");
        });
    }
    , numericOnly: function (elementName) {
        $(elementName).keyup(function () {
            let val = $(this).val();
            val = val.replace(/[^0-9.]/g, "");
            $(this).val(val);
            $(this).attr("secret-val", val)
        })
    }
    , invalidTextListener: function (inputSelector, invalidHintSelector, validator) {
        const changeState = this.changeState;
        $(inputSelector).keyup(function () {
            const value = $(this).val();
            const valid = validator(value);

            changeState(!valid, invalidHintSelector);
        });
    }
    , changeState: function (show, selectorName) {
        let element = $(selectorName);

        if (true === show) {
            element.fadeIn(500, function () {
                $(this).show();
            });

        } else {
            element.fadeOut(500, function () {
                $(this).hide();
            });
        }
    },
};