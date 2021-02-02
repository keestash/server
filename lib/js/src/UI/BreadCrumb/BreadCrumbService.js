export class BreadCrumbService {

    clear() {
        $("#breadcrumb").html('');
        $("#breadcrumb").html('<li class="breadcrumb-item invisible">breadcrumb</li>');
    }

    parse(values, callable) {
        const html = [];

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
        $("#breadcrumb").html('');
        $("#breadcrumb").html(html.join(''));

        $(".breadcrumb-item").off("click").click(
            (event) => {
                callable($(event.target).data('node-id'))
            }
        );
    }
}