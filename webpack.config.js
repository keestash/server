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
const {VueLoaderPlugin} = require("vue-loader");
const path = require("path");

const env = process.env.NODE_ENV;

const baseModule = {
    mode: env,
    entry: {
        base: ['babel-polyfill', './lib/js/src/base.js']
    },
    output: {
        path: __dirname + "/public/js/",
        filename: '[name].bundle.js',
        chunkFilename: '[name].chunk.js',
    },
    optimization: {
        splitChunks: {
            chunks: 'async'
        },
        usedExports: true,
    },
    module: {
        rules: [
            {
                test: /\.vue$/,
                exclude: path.resolve(__dirname, "node_modules"),
                loader: ['vue-loader']
            },
            {
                test: /\.js$/,
                exclude: path.resolve(__dirname, "node_modules"),
                loader: ['babel-loader']
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
        new VueLoaderPlugin()
    ]
};

const webpackConfig = [].concat(
    baseModule,
    toConfig(appModules, baseModule)
);


module.exports = webpackConfig;

function toConfig(modules, baseModule) {
    let conf = [];
    for (let i = 0; i < modules.length; i++) {
        const configPath = modules[i];
        const config = require(configPath);

        config.mode = baseModule.mode;
        config.node = baseModule.node;
        config.module = baseModule.module;
        config.resolve = baseModule.resolve;
        config.output = baseModule.output;
        config.plugins = baseModule.plugins;
        config.optimization = baseModule.optimization;
        conf.push(config);
    }
    return conf;
}

