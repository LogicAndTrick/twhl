
$(function() {

    var round = function (val, num)
    {
        var pow = Math.pow(10, num);
        return Math.round(val * pow) / pow;
    }

    var format_filesize = function(bytes)
    {
        if (bytes < 1024) return bytes + 'b';
        var kbytes = bytes / 1024;
        if (kbytes < 1024) return round(kbytes, 2) + 'kb';
        var mbytes = kbytes / 1024;
        if (mbytes < 1024) return round(mbytes, 2) + 'mb';
        var gbytes = mbytes / 1024;
        if (gbytes < 1024) return round(gbytes, 2) + 'gb';
        var tbytes = gbytes / 1024;
        if (tbytes < 1024) return round(tbytes, 2) + 'tb';
        var pbytes = tbytes / 1024;
        return round(pbytes, 2) + 'pb';
    }

    const c = $('.wiki.bbcode');
    if (c.length === 1) {
        var pages = Array.from(new Set(c.find('a[href*="://' + window.location.host + '/wiki/page/"]').map(function (i, x) { return decodeURIComponent(x.href.replace(/^.*\//ig, '')); }).filter(function (i, x) { return !x.match(/^category:/ig); })));
        var embeds = Array.from(new Set(c.find('.embedded-inline.download a').map(function (i, x) { return decodeURIComponent(x.href.replace(/^.*\//ig, '')); })));
        if (pages.length > 0 || embeds.length > 0) {
            var data = {pages: pages, embeds: embeds};
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
                            var hr = decodeURI(x.href), uc = '://' + window.location.host + '/wiki/page/' + p.slug;
                            return hr && hr.toLowerCase().indexOf(uc.toLowerCase()) >= 0;
                        });
                        if (!p.exists) pl.addClass('text-danger').attr('title', 'Page does not exist yet - click to create it');
                    }
                    for (var eo in data.embeds) {
                        var e = data.embeds[eo];
                        var el = c.find('a[href*="://' + window.location.host + '/wiki/embed/"]').filter(function (i, x) {
                            var hr = decodeURI(x.href), uc = '://' + window.location.host + '/wiki/embed/' + e.slug;
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

});