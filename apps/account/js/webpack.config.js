const glob = require("glob");
const webpack = require("webpack");

console.error(__dirname);

module.exports = {
    entry: {
        account: glob.sync(__dirname + "/src/account/*.js")
        , security: glob.sync(__dirname + "/src/security/*.js")
    },

    output: {
        path: __dirname + "/dist/",
        filename: '[name].bundle.js'
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