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
import BreadCrumb from "./BreadCrumb";
import Parser from "../UI/Template/Parser";
import DataNode from "../Data/DataNode";

export class BreadCrumbParser {

    parse(nodes) {
        const breadCrumbTemplate = DataNode.getValue("data-breadcrumb-template");
        const x = this.toBreadCrumbs(nodes);
        const parsed = Parser.parse(breadCrumbTemplate, {"bc": x});
        $("#breadcrumb__wrapper").html(parsed);
        this.changeListener();
    }

    toBreadCrumbs(nodes) {
        let list = [];
        $.each(nodes, function (i, v) {
            list.push(new BreadCrumb(v.id, v.name));
        });
        return list;
    }

    changeListener() {
        const _this = this;
        $("#tl__breadcrumbs a").off().each(function (i, v) {
            const that = this;
            $(v).off("click").off().on("click", function () {
                _this.thisIsAVeryLongName(that);
            });
        })
    }

    thisIsAVeryLongName() {
        // silence is golden
    }

    register(listener) {
        this.thisIsAVeryLongName = () => {
        };
        this.thisIsAVeryLongName = listener;
    }
}