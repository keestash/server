import {Base, HAS_DATA_DIRS, READABLE_DIRS, WRITABLE_DIRS} from "./Base";
import {RESPONSE_CODE_OK} from "../../../../../lib/js/src/UI/ModalHandler";
import modal from "../../../../../lib/js/src/UI/modal";

export class HasDataDirs extends Base {

    constructor(
        formula
        , routes
        , lazyOperator
    ) {
        super(
            "ii__has__data__dirs__segment"
            , "ii__has__data__dirs__segment__dimmer"
            , formula
            , {
                "name": HAS_DATA_DIRS
                , "route": routes.getHasDataDirs()
                , "template_name": "has_data_dirs_template"
            }
        );

        this.routes = routes;
        this.lazyOperator = lazyOperator;
        this.buttonId = "#ii__has__data__dirs__submit";
        this.listenToEvent();
    }

    listenToEvent() {
        const _this = this;
        $(document).on(WRITABLE_DIRS, function () {

            _this.lazyOperator.doAfterElementDisplayed(
                _this.buttonId
                , 250
                , function (selector) {
                    $(selector).removeClass("disabled");
                }
            );

        });
    }

    initFormSubmit(strings) {
        const _this = this;

        $(_this.buttonId).ready(function () {
            $(_this.buttonId).click(function (e) {
                e.preventDefault();

                _this.formula.get(
                    _this.getKeys().route
                    , {}
                    , function (x, y, z) {
                        const object = JSON.parse(x);

                        if (RESPONSE_CODE_OK in object) {

                            const messages = object[RESPONSE_CODE_OK]['messages'];
                            let earlyReturn = false;

                            if (HAS_DATA_DIRS in messages) {
                                let l = JSON.parse(messages[HAS_DATA_DIRS]).length;

                                if (l !== 0) {
                                    alert('still not there! Please check again!');
                                    earlyReturn = true;
                                }
                            }

                            if (true === earlyReturn) {
                                return;
                            }

                            $(this).parent().remove().fadeOut(3000);
                            $(this).parent().append(strings.updated);
                            _this.triggerEvent();
                        } else {
                            modal.miniModal("The dirs are still not readable/writable. Please try again!");
                        }
                    }
                    , function (x, y, z) {
                        console.log(x)
                    }
                )
            });
        });
    }
}