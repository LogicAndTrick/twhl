const mix = require('laravel-mix');

mix.scripts([
        'node_modules/jquery/dist/jquery.js',
        'node_modules/jquery-appear-original/index.js',            // Lazy loading vault embeds
        'node_modules/js-cookie/src/js.cookie.js',
        'resources/assets/js/lib/jquery.plugin.js',               // Required for countdown
        'resources/assets/js/lib/jquery.countdown.js',            // Compo pages
        'resources/assets/js/lib/jquery-ui.min.js',
        'resources/assets/js/lib/jquery.ba-throttle-debounce.js',
        'resources/assets/js/lib/Chart.js',                       // Poll results
        'node_modules/bootstrap/dist/js/bootstrap.bundle.js',
        'node_modules/vue/dist/vue.js',
        'resources/assets/js/lib/bootbox.js',
        'resources/assets/js/lib/dropzone.js',
        'node_modules/select2/dist/js/select2.js',
        'resources/assets/js/lib/select2-data-api.js',
        'resources/assets/js/lib/select2-pagination.js',
        'resources/assets/js/lib/autocomplete.js',
        'resources/assets/js/lib/nano-templating.js',
        'resources/assets/js/lib/jsdifflib.js',
        'resources/assets/js/lib/jssor.slider.mini.js',
        'resources/assets/js/lib/autolinker.js',
        'resources/assets/js/lib/highlight.pack.js',
        "resources/assets/js/boot/christmas.js",
        "resources/assets/js/boot/comment-meta-rating.js",
        "resources/assets/js/boot/embed.js",
        "resources/assets/js/boot/embed-inline.js",
        "resources/assets/js/boot/highlight.js",
        "resources/assets/js/boot/home.js",
        "resources/assets/js/boot/shoutbox.js",
        "resources/assets/js/boot/util.js",
        "resources/assets/js/boot/wiki-contents.js",
        "resources/assets/js/boot/wikicode-preview.js"
    ], 'public/js/all.js')
    .sass('resources/assets/sass/app.scss', 'public/css')
         .options({
              processCssUrls: false
           })
    .version();
