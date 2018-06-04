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

    elixir.config.css.sass.pluginOptions = {
        precision: 6,
        outputStyle: 'expanded'
    };

    elixir.config.css.autoprefix = {
        options: {
            browsers: [
                'Chrome >= 35',
                'Firefox >= 38',
                'Edge >= 12',
                'Explorer >= 10',
                'iOS >= 8',
                'Safari >= 8',
                'Android 2.3',
                'Android >= 4',
                'Opera >= 12'
            ]
        }
    };


    //mix.less([
    //    'app.less'
    //]);
    mix.sass([
        'app.scss'
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
        '../../../node_modules/tether/dist/js/tether.js',
        '../../../node_modules/bootstrap/dist/js/bootstrap.js',
        '../../../node_modules/vue/dist/vue.js',
        'lib/bootbox.js',
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
