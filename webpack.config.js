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
        base: ['./lib/js/src/base.js']
    },
    output: {
        path: __dirname + "/public/js/",
        filename: '[name].bundle.js',
        chunkFilename: '[name].chunk.js',
        assetModuleFilename: '../img/[hash][ext][query]'
    },
    module: {
        rules: [
            {
                test: /\.vue$/,
                exclude: path.resolve(__dirname, "node_modules"),
                use: [
                    {
                        loader: 'vue-loader'
                    }
                ]
            },
            {
                test: /\.js$/,
                exclude: path.resolve(__dirname, "node_modules"),
                use: [
                    {
                        loader: 'babel-loader'
                    }
                ]
            },
            {
                test: /\.s[ac]ss$/i,
                use: [
                    // Creates `style` nodes from JS strings
                    "style-loader",
                    // Translates CSS into CommonJS
                    "css-loader",
                    // Compiles Sass to CSS
                    "sass-loader",
                ],
            },
            {
                test: /\.(png)$/,
                use: {
                    loader: 'url-loader',
                },
            },
        ]
    },
    resolve: {
        alias: {
            'vue': '@vue/runtime-dom',
            // 'Vue': 'vue/dist/vue.esm-bundler.js'
        },
        extensions: ['.tsx', '.ts', '.js', '.vue'],
        fallback: {
            fs: false
        }
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
        conf.push(config);
    }
    return conf;
}

