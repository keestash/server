export class Container {

    constructor() {
        this.container = {};
        this.cache = {};
    }

    register(name, callback) {
        this.container[name] = callback;
    }

    query(name) {

        if (name in this.cache) {
            return this.cache[name];
        }

        const container = this;
        if (this.container.hasOwnProperty(name)) {
            const object = this.container[name](container);
            this.cache["name"] = object;
            return object;
        }
        return null;
    }
}
