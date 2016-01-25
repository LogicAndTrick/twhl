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
    elixir.config.production = true;

    mix.less([
        'app.less'
    ]);
    mix.scripts([
        'lib/jquery.js',
        'lib/jquery.cookie.js',
        'lib/jquery.mousewheel.js',
        'lib/jquery.appear.js',
        'lib/jquery.plugin.js',
        'lib/jquery.countdown.js',
        'lib/jquery-ui.min.js',
        'lib/Chart.js',
        'lib/bootstrap.js',
        'lib/bootbox.min.js',
        'lib/dropzone.js',
        'lib/select2.js',
        'lib/select2-data-api.js',
        'lib/select2-pagination.js',
        'lib/autocomplete.js',
        'lib/nano-templating.js',
        'lib/jsdifflib.js',
        'lib/jssor.slider.mini.js',
        'lib/autolinker.js',
        "boot/*.js"
    ]);
});
