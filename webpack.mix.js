const mix = require('laravel-mix');


/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

const MomentLocalesPlugin = require('moment-locales-webpack-plugin');
// const TargetsPlugin = require("targets-webpack-plugin");


mix.js('resources/src/main.js', 'public').js('resources/src/login.js', 'public')
    .sass('resources/src/assets/styles/sass/globals/globals.scss', 'public/css');

    mix.webpackConfig({
        output: {
          
            filename:'js/[name].min.js',
            chunkFilename: 'js/bundle/[name].js',
          },
        plugins: [
            new MomentLocalesPlugin(),
            //  new TargetsPlugin({
            //     browsers: [
            //         "> 1%",
            //         "last 2 versions",
            //         "not ie <= 8",
            //         "chrome >= 41",
            //         "IE 11"]
            // }),
        ]
    });
