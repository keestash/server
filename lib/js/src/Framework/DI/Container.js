export class Container {

    constructor() {
        this.cache = {};
        this.container = {};
    }

    register(name, callback) {
        this.container[name] = callback;
    }

    query(name) {
        if (this.cache.hasOwnProperty(name)) {
            return this[name];
        }

        if (!this.container.hasOwnProperty(name)) {
            return null;
        }

        const callback = this.container[name];
        const service = callback();
        this.cache[name] = service;
        return service;
    }
}