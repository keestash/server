/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
const glob = require("glob");
const webpack = require("webpack");
const VueLoaderPlugin = require('vue-loader/lib/plugin')
const appModules = glob.sync("./apps/*/js/webpack.config.js");

const env = process.env.NODE_ENV;

const baseModule = {
    mode: env,
    entry: {
        base: ['babel-polyfill', './lib/js/src/base.js']
    },
    output: {
        path: __dirname + "/lib/js/dist",
        filename: 'base.bundle.js'
    },
    module: {
        rules: [
            {
                test: /\.vue$/,
                loader: 'vue-loader'
            },
            {
                exclude: /node_modules/,
                loader: 'babel-loader',
            },
            {
                test: /\.js$/,
                loader: 'babel-loader'
            },
            {
                test: /\.scss$/,
                use: [
                    'vue-style-loader',
                    'css-loader',
                    {
                        loader: 'sass-loader',
                    },
                ],
            }
        ]
    },
    plugins: [
        new webpack.ProvidePlugin({
            $: "jquery",
            jQuery: "jquery"
        }),
        new VueLoaderPlugin()
    ],
    resolve: {
        alias: {
            handlebars: 'handlebars/dist/handlebars.min.js',
            'vue$': 'vue/dist/vue.esm.js'
        },
        extensions: ['.js', '.vue'],
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
