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
import Twig from "twig";

export class Parser {

    parse(raw, context) {
        let template = Twig.twig({
            // id: "list", // id is optional, but useful for referencing the template later
            data: raw
        });

        if (typeof template === 'undefined') {
            console.log("i am undefined");
            console.log(template);
            console.log(raw);
            console.log(context);
        }

        return template.render(context);
    }
}
