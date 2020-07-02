/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
import $ from "jquery";
import 'bootstrap/dist/js/bootstrap';

export class Long {

    constructor(
        templateLoader
        , templateParser
    ) {
        this.templateLoader = templateLoader;
        this.templateParser = templateParser;
    }

    async show(
        header
        , buttonText
        , description
        , eventName
    ) {
        this.templateLoader.load(true);
        const templates = await this.templateLoader.read();
        const template = templates['long'];

        let parsedTemplate = this.templateParser.parse(
            template
            , {
                header: header
                , description: description
                , buttonText: buttonText
            }
        );

        const modal = $(parsedTemplate);

        modal.on(
            'shown.bs.modal'
            , (e) => {

                modal.find(".btn-primary").click(
                    () => {
                        console.log("ok clicked")
                        return $(document).trigger(
                            eventName
                            , [
                                modal
                            ]
                        );
                    });

            })

        modal.modal(
            {
                backdrop: false
                , keyboard: true
                , focus: true
                , show: true
            }
        );

        // this.element
        //     .modal(
        //         {
        //             onApprove: () => {
        //                 return $(document).trigger(
        //                     eventName
        //                     , [
        //                         this.element
        //                     ]
        //                 );
        //             }
        //             , onShow: () => {
        //
        //             }
        //             , onDeny: () => {
        //                 _this.element.remove();
        //             }
        //             , closable: true
        //             , allowMultiple: false
        //         }
        //     )
        //     .modal("show")
        // ;
    }

}
