export default function Router() {
    this.routeTo = function (name) {
        let routeTo = Keestash.Main.getHost() + name;
        window.location.replace(routeTo);
    };

    this.route = function (url) {
        window.location.replace(url);
    }
}