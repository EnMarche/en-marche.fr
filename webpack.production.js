const fs = require('fs');
const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require("terser-webpack-plugin");
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const merge = require('webpack-merge');
const common = require('./webpack.common');

module.exports = merge(common, {
    mode: 'production',
    output: {
        filename: '[hash].[name].js',
        chunkFilename: '[chunkhash].[name].js',
    },
    optimization: {
        minimizer: [
            new TerserPlugin({
                parallel: true,
            }),
            new OptimizeCSSAssetsPlugin({}),
        ],
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: '[name].css',
            chunkFilename: '[hash].app.css',
        }),
        function symfonyAssetsVersion() {
            this.plugin('done', (stats) => {
                fs.writeFile(
                    path.join(__dirname, 'config/packages/', 'assets_version.yaml'),
                    `parameters:\n    assets_hash: ${stats.hash}\n`,
                    (err) => { if (err) throw err; }
                );
            });
        },
    ],
});
