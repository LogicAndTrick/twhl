
$(function() {

    const round = function (val, num)
    {
        const pow = Math.pow(10, num);
        return Math.round(val * pow) / pow;
    }

    const format_filesize = function(bytes)
    {
        if (bytes < 1024) return bytes + 'b';
        const kbytes = bytes / 1024;
        if (kbytes < 1024) return round(kbytes, 2) + 'kb';
        const mbytes = kbytes / 1024;
        if (mbytes < 1024) return round(mbytes, 2) + 'mb';
        const gbytes = mbytes / 1024;
        if (gbytes < 1024) return round(gbytes, 2) + 'gb';
        const tbytes = gbytes / 1024;
        if (tbytes < 1024) return round(tbytes, 2) + 'tb';
        const pbytes = tbytes / 1024;
        return round(pbytes, 2) + 'pb';
    }

    const c = $('.wiki.bbcode');
    if (c.length === 1) {
        const pages = Array.from(new Set(c.find('a[href*="://' + window.location.host + '/wiki/page/"]')
            .map(function (i, x) {
                /** @var string */
                let url = x.href;
                if (url.indexOf('#') >= 0) {
                    const spl = url.split('#');
                    url = spl[0];
                }
                return decodeURIComponent(url.replace(/^.*\//ig, ''));
            })
            .filter(function (i, x) {
                return !x.match(/^category:/ig);
            })));
        const embeds = Array.from(new Set(c.find('.embedded-inline.download a')
            .map(function (i, x) {
                return decodeURIComponent(x.href.replace(/^.*\//ig, ''));
            })));
        if (pages.length > 0 || embeds.length > 0) {
            const data = {pages: pages, embeds: embeds};
            $.ajax({
                type: "POST",
                url: '/api/wiki-objects/page-information',
                data: JSON.stringify(data),
                dataType: 'json',
                contentType: 'application/json',
                success: function (data) {
                    for (var po in data.pages) {
                        var p = data.pages[po];
                        var pl = c.find('a[href*="://' + window.location.host + '/wiki/page/"]').filter(function (i, x) {
                            var hr = decodeURIComponent(x.href), uc = '://' + window.location.host + '/wiki/page/' + p.slug;
                            return hr && hr.toLowerCase().endsWith(uc.toLowerCase());
                        });
                        if (!p.exists) pl.addClass('text-danger').attr('title', 'Page does not exist yet - click to create it');
                        else pl.attr('title', p.revision.title);
                    }
                    for (var eo in data.embeds) {
                        var e = data.embeds[eo];
                        var el = c.find('a[href*="://' + window.location.host + '/wiki/embed/"]').filter(function (i, x) {
                            var hr = decodeURIComponent(x.href), uc = '://' + window.location.host + '/wiki/embed/' + e.slug;
                            return hr && hr.toLowerCase().indexOf(uc.toLowerCase()) >= 0;
                        });
                        if (!e.exists) {
                            el.addClass('text-danger').attr('title', 'File does not exist');
                        } else {
                            var sz = e.meta.filter(function (m) { return m.key === 's'; });
                            var size = sz && sz.length > 0 && parseInt(sz[0].value, 10);
                            if (size) el.append(' <span>(' + format_filesize(size) + ')</span>');
                            else el.append(' <span>(Unknown size)</span>');
                        }
                    }
                }
            });
        }
    }

    $('video[autoplay]').each((i, t) => {
        if (!t.paused) return;
        const ol = $('<div class="autoplay-overlay">Video - Click to play</div>');
        ol.insertBefore(t);
    }).one('play', event => {
        $(event.currentTarget).siblings('.autoplay-overlay').remove();
    });

});