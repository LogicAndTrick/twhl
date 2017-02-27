$(document).on('click', '.video-content .uninitialised', function(event) {
    var $t = $(this),
      ytid = $t.data('youtube-id'),
       url = 'https://www.youtube.com/embed/' + ytid + '?autoplay=1&rel=0',
     frame = $('<iframe></iframe>').attr({ src: url, frameborder: 0, allowfullscreen: ''}).addClass('caption-body');
    $t.replaceWith(frame);
});


var embed_templates = {
    vault_slider_screenshot: '<div><img data-u="image" data-src2="{image_large}" alt="Screenshot" /><img data-u="thumb" data-src2="{image_thumb}" alt="Thumbnail" /></div>',
    vault_slider: '<div id="{slider_id}" class="slider">' +
            '<div class="loading" data-u="loading">Loading...</div>' +
            '<div class="slides" data-u="slides">{slider_screenshots}</div>' +
            '<div data-u="thumbnavigator" class="thumbs"><div data-u="slides"><div data-u="prototype" class="p"><div data-u="thumbnailtemplate" class="i"></div></div></div></div>' +
            '<span data-u="arrowleft" class="arrow left" style="top: 123px; left: 8px;"></span><span data-u="arrowright" class="arrow right" style="top: 123px; right: 8px;"></span>' +
            '</div>',
    vault: '<div class="slot">' +
            ' <div class="slot-heading mb-3">' +
            '  <div class="pull-right text-center" title="{game_name}"><img class="game-icon" src="{game_image}" alt="{game_name}"><small class="d-block">{game_abbr}</small></div>' +
            '  <div class="slot-avatar"><span class="avatar small" title="{user_name}"><a href="{user_url}"><img src="{user_avatar}" alt="{user_name}"></a></span></div>' +
            '  <div class="slot-title"><a href="{url}">{name}</a> by <a href="{user_url}">{user_name}</a></div>' +
            '  <div class="slot-subtitle">Posted {created} &bull; {category} &bull; {game_name}</div>' +
            ' </div>' +
            ' {vault_slider}' +
            '</div>',
    vault_no_slider: '<a href="{url}"><img class="embed-image" src="{shot}" alt="Screenshot" /></a>'
};

var __uniq = 0;
var embed_callbacks = {
    vault: function (element, data) {
        var images = data.vault_screenshots || [];
        images.forEach(function (img) {
            img.image_large = template(window.urls.embed.vault_screenshot, { shot: img.image_large });
            img.image_thumb= template(window.urls.embed.vault_screenshot, { shot: img.image_thumb });
        });

        if (images.length <= 1) {
            // 1 or zero screenshots - no need for a slide show
            // Just put screenshot image in there
            var shot = images.length == 1 ? images[0].image_large : window.urls.images.no_screenshot_320;
            var embed_no_slider = template(embed_templates.vault, {
                url: template(window.urls.view.vault, data),
                name: data.name,
                user_url: template(window.urls.view.user, data.user),
                user_avatar: data.user.avatar_inline,
                user_name: data.user.name,
                vault_slider: template(embed_templates.vault_no_slider, { shot: shot, url: template(window.urls.view.vault, data) })
            });

            // Set the content
            element.html(embed_no_slider);
        } else {

            var sid = 'vault-slider-'+(__uniq++);

            // Sort the shots
            images.sort(function(a, b) {
                if (a.is_primary) return -1;
                if (b.is_primary) return +1;
                return a.order_index - b.order_index;
            });

            // Assemble the slide show
            var shots = images.map(function(s) {
                return template(embed_templates.vault_slider_screenshot, s);
            }).join('');
            var show = template(embed_templates.vault_slider, { slider_id: sid, slider_screenshots: shots });
            var embed = template(embed_templates.vault, {
                url: template(window.urls.view.vault, data),
                name: data.name,
                user_url: template(window.urls.view.user, data.user),
                user_avatar: data.user.avatar_small,
                user_name: data.user.name,
                created: readableTime(Date.parse(data.updated_at)),
                category: data.vault_category.name,
                game_abbr: data.game.abbreviation,
                game_name: data.game.name,
                game_image: template(window.urls.embed.game_icon, { game_abbr: data.game.abbreviation }),
                vault_slider: show
            });

            // Set the content
            element.html(embed);

            // Initialise the slide show
            var slider = new $JssorSlider$(sid, {
                $AutoPlay: true,
                $AutoPlayInterval: 4000,
                $SlideDuration: 250,
                $FillMode: 5,

                $ThumbnailNavigatorOptions: {
                    $Class: $JssorThumbnailNavigator$,
                    $ChanceToShow: 2,
                    $SpacingX: 8,
                    $DisplayPieces: 10,
                    $ParkingPosition: 360
                },

                $ArrowNavigatorOptions: {
                    $Class: $JssorArrowNavigator$,
                    $AutoCenter: 2
                }
            });
        }
    }
};

$(document).on('appear', '.embed-content .uninitialised', function(e, $affected) {
  var $t = $(this).text('Loading...'),
     par = $t.parent(),
     typ = $t.data('embed-type'),
      id = $t.data(typ+'-id'),
     url = window.urls.embed[typ];

    if ($t.data('stop')) return;
    $t.data('stop', true);

    $.get(url, { id: id, expand: 'user,vault_screenshots,game,vault_category' }).done(function(data) {
        embed_callbacks[typ].call(window, par, data[0]);
    });
});

$(function() {
    $('.embed-content .uninitialised').appear();
    // Force already-visible stuff to appear
    setTimeout($.force_appear, 10);
});