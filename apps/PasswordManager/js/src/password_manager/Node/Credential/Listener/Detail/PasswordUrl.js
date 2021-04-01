/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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
import {Text} from "./Text";

export class PasswordUrl extends Text {

    constructor(urlService) {
        const id = "pwm__login__website";
        super(id);
        this.id = id;
        this.button = $("#pwm__url__button");
        this.urlService = urlService;
    }

    listen() {
        const _this = this;

        this.button.one(
            "click",
            () => {
                const url = $("#" + _this.id).val();
                const win = window.open(
                    _this.urlService.sanitizeUrl(url), '_blank');
                win.focus();
            }
        )

        super.listen();
    }
}
