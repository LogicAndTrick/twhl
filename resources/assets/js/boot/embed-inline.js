
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

    $('.embedded-inline.download[data-info]').each(function() {
        var $t = $(this);
        var $a = $t.find('a');
        var iurl = $t.data('info');
        $.getJSON(iurl, function (d) {
            if (!d.exists) {
                $a.addClass('text-danger').append(' <span>(File not found)</span>');
            } else {
                var sz = d.meta.filter(function (m) { return m.key == 's'; });
                var size = sz && sz.length > 0 && parseInt(sz[0].value, 10);
                if (size) $a.addClass('text-success').append(' <span>(' + format_filesize(size) + ')</span>');
                else $a.addClass('text-success').append(' <span>(Unknown size)</span>');
            }
        });
    });

});