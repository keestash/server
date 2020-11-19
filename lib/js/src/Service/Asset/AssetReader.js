export class AssetReader {

    constructor(stringLoader, templateLoader) {
        this.stringLoader = stringLoader;
        this.templateLoader = templateLoader;
    }

    async read(loadFirst = false) {

        if (true === loadFirst) {
            await this.templateLoader.load(true);
            await this.stringLoader.load(true);
        }

        const templates = await this.templateLoader.read();
        const strings = await this.stringLoader.read();

        return [templates, strings];
    }
}