export class SearchPasswordList {


    init() {
        const _this = this;
        $('#pwm_search_passwords').on(
            'input'
            , (e) => {
                const val = $('#pwm_search_passwords').val();
                const tableBody = $("#table__body");

                $.each(
                    tableBody.children()
                    , (index, value) => {
                        const valueObject = $(value);
                        let content = valueObject.text().replace(/\s+/g, ' ');

                        if (
                            val === ""
                            || content.toLowerCase().indexOf(val.toLowerCase()) > 0
                        ) {
                            valueObject.addClass("d-flex");
                            valueObject.removeClass("d-none")
                        } else {
                            valueObject.removeClass("d-flex");
                            valueObject.addClass("d-none");
                        }
                    }
                );
            }
        );

    }

}
