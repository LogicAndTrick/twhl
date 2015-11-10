var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Less
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    mix.less([
        'app.less'
    ]);
    mix.scripts([
        'lib/jquery.js',
        'lib/jquery.cookie.js',
        'lib/jquery.mousewheel.js',
        'lib/bootstrap.js',
        'lib/select2.js',
        'lib/select2-data-api.js',
        'lib/select2-pagination.js',
        'lib/autocomplete.js',
        'lib/nano-templating.js',
        'lib/jsdifflib.js',
        'lib/jssor.slider.mini.js',
        "boot/*.js"
    ]);
});
