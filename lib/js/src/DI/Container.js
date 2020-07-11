export class Container {

    constructor() {
        this.container = {};
    }
    register(name, callback){
        this.container[name] = callback;
    }

    query(name){
        const container = this;
        if (this.container.hasOwnProperty(name)) {
            return this.container[name](container);
        }
        return null;
    }
}