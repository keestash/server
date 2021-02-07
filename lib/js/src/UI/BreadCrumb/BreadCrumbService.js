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
export class BreadCrumbService {

    show() {
        $("#breadcrumb-wrapper").removeClass("d-none");
    }

    hide() {
        $("#breadcrumb-wrapper").addClass("d-flex");
    }

    clear() {
        const breadCrumb = $("#breadcrumb");
        breadCrumb.html('');
        breadCrumb.html('<li class="breadcrumb-item invisible">breadcrumb</li>');
    }

    parse(values, callable) {
        const html = [];
        const breadCrumb = $("#breadcrumb");

        for (let i = 0; i < values.length; i++) {
            const val = values[i];
            let name = val.name;
            if (val.is_root) {
                name = 'Home';
            }
            html.push(
                '<li class="breadcrumb-item" data-node-id="' + val.id + '">' + name + '</li>'
            )
        }
        // <li className="breadcrumb-item"><a href="#">Home</a></li>
        // <li className="breadcrumb-item">Check24</li>
        // <li className="breadcrumb-item active" aria-current="page">HHV</li>
        breadCrumb.html('');
        breadCrumb.html(html.join(''));

        $(".breadcrumb-item").off("click").click(
            (event) => {
                callable($(event.target).data('node-id'))
            }
        );
    }
}