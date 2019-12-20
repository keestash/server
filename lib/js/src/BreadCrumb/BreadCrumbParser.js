import DataNode from "../Data/DataNode";
import Parser from "../UI/Template/Parser";
import BreadCrumb from "./BreadCrumb";

export default {
    thisIsAVeryLongName: function () {
    },
    register: function (listener) {
        this.thisIsAVeryLongName = listener;
    },
    addBreadcrumbs: function (nodes) {
        const breadCrumbTemplate = DataNode.getValue("data-breadcrumb-template");
        const x = this.toBreadCrumbs(nodes);
        const parsed = Parser.parse(breadCrumbTemplate, {"bc": x});
        $("#breadcrumb__wrapper").html(parsed);
        this.changeListener();
    }
    , toBreadCrumbs(nodes) {

        let list = [];
        $.each(nodes, function (i, v) {
            list.push(new BreadCrumb(v.id, v.name));
        });
        return list;
    }
    , changeListener: function () {

        $("#tl__breadcrumbs a").each(function (i, v) {
            const that = this;
            $(v).off("click").on("click", function () {
                Keestash.Observer.BreadCrumbParser.thisIsAVeryLongName(that);
            });
        })
    }


}
