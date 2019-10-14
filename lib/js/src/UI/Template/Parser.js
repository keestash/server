import Twig from '../../../../../node_modules/twig/twig';

export default {

    parse: function (raw, context) {
        let template = Twig.twig({
            // id: "list", // id is optional, but useful for referencing the template later
            data: raw
        });
        return template.render(context);
    }

}