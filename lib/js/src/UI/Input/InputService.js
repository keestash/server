/**
 * Keestash
 *
 * Copyright (C) <2020> <Dogan Ucar>
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

export class InputService {

    constructor() {
        this.invalidColor = '#CC7F7F';
    }

    invalid(elementName) {
        $(elementName).css('border-color', this.invalidColor);
        this.shake(elementName);
        this.addRevertListener(elementName);
    }

    shake(element) {
        element.each(() => {

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
        element.css("border-color", this.invalidColor);
    }

    addRevertListener(elementName) {
        $(elementName).on("input", () => {
            $(elementName).css('border-color', '');
            $(elementName).off("input");
        });
    }
}
