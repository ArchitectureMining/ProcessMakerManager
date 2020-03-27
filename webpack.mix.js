const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public_html/manager/js')
   .sass('resources/sass/app.scss', 'public_html/manager/css');