const glob = require("glob");
const webpack = require("webpack");

module.exports = {
    entry: glob.sync(__dirname + "/src/*.js"),
    output: {
        path: __dirname + "/dist/",
        filename: 'about.bundle.js'
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
    ]
};