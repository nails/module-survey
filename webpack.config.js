const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const path = require('path');


module.exports = {
    entry: {
        'admin': './assets/js/admin.js',
        'admin.survey.edit': './assets/js/admin.survey.edit.js',
        'admin.survey.stats': './assets/js/admin.survey.stats.js',
        'admin.survey.stats.charts': './assets/js/admin.survey.stats.charts.js',
        'stats': './assets/js/stats.js',
        'survey': './assets/js/survey.js',
    },
    output: {
        filename: '[name].min.js',
        path: path.resolve(__dirname, 'assets/js/')
    },
    module: {
        rules: [
            {
                test: /\.(css|scss|sass)$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader',
                        options: {
                            minimize: true,
                            importLoaders: 2,
                            url: false
                        }
                    },
                    {
                        loader: 'postcss-loader',
                        options: {
                            plugins: () => [require('autoprefixer')]
                        }
                    },
                    'sass-loader'
                ]
            },
        ]
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: '../css/[name].min.css',
            allChunks: true
        }),
    ],
    mode: 'production'
};
