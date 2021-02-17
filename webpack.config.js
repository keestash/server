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
const appModules = glob.sync("./apps/*/js/webpack.config.js");
const appSettings = glob.sync("./apps/*/js/src/Settings/settings.js");
const {VueLoaderPlugin} = require("vue-loader");

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
                exclude: /node_modules/,
                loader: 'babel-loader',
            },
            {
                test: /\.js$/,
                loader: 'babel-loader'
            },
            {
                test: /\.vue$/,
                exclude: /node_modules/,
                loader: ['vue-loader']
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
            },
        ]
    },
    resolve: {
        alias: {
            'vue$': 'vue/dist/vue.esm.js'
        },
        extensions: ['.js', '.vue'],
    },
    node: {
        fs: 'empty'
    },
    plugins: [
        new VueLoaderPlugin(),
        new webpack.ProvidePlugin({
            $: "jquery",
            jQuery: "jquery"
        })
    ]
};

const webpackConfig = [].concat(
    baseModule
    , toConfig(appModules, baseModule)
);

module.exports = webpackConfig;

function toConfig(modules, baseModule) {
    let conf = [];
    for (let i = 0; i < modules.length; i++) {
        const configPath = modules[i];
        const config = require(configPath);
        config.mode = baseModule.mode;
        config.node = {fs: 'empty'};
        config.module = Object.assign(baseModule.module, config.module || {});
        config.resolve = Object.assign(baseModule.resolve, config.resolve || {});
        conf.push(config);
    }
    return conf;
}
