export class PromiseTest {

    loadTemplates() {

        const arr = {};
        arr["1"] = 1;
        arr["2"] = 1;
        arr["3"] = 1;
        return arr
    }

    loadAll() {

        const loaderPromise = new Promise(
            (resolve) => {
                const arr = this.loadTemplates();
            }
        )

    }
}