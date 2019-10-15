const glob = require("glob");
const webpack = require("webpack");
const appModules = glob.sync("./apps/*/js/webpack.config.js");

const baseModule = {
    entry: {
        base: './lib/js/src/base.js',
    },
    output: {
        path: __dirname + "/lib/js/dist",
        filename: 'base.bundle.js'
    },
    module: {
        rules: [{
            exclude: /node_modules/,
            loader: 'babel-loader'
        }]
    },
    plugins: [
        new webpack.ProvidePlugin({
            $: "jquery",
            jQuery: "jquery"
        })
    ],
    resolve: {
        alias: {
            handlebars: 'handlebars/dist/handlebars.min.js'
        }
    },
    node: {
        fs: 'empty'
    }
};

const webpackConfig = [].concat(
    baseModule
    , toConfig(appModules)
);

module.exports = webpackConfig;

function toConfig(modules) {
    let conf = [];
    for (let i = 0; i < modules.length; i++) {
        const configPath = modules[i];
        const config = require(configPath);
        config.node = {fs: 'empty'};
        conf.push(config);
    }
    return conf;
}