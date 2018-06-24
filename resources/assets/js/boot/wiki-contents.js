
$(function() {

    var autoId = 1;

    $('.wiki.bbcode').each(function() {
        var $t = $(this);
        var headings = $t.find('> h1, > h2, > h3, > h4, > h5, > h6');
        if (headings.length  > 5) {
            var toc = $('<div/>').addClass('contents');
            var list = $('<ul/>');
            toc.append(list);
            list.append('<li><h2>Contents</h2></li>')

            var lvl = 1, lastLvl = 10;
            for (var i = 0; i < headings.length; i++) {
                var h = $(headings[i]);

                if (!h.attr('id')) h.attr('id', 'wiki-heading-' + autoId++);

                var hlevel = parseInt(h.get(0).tagName.substring(1), 10);
                if (hlevel > lastLvl) lvl++;
                else lvl = 1;
                lastLvl = hlevel;

                list.append('<li class="level-' + lvl + '"><a href="#' + h.attr('id') + '">' + h.text() + '</a></li>');
            }
            $t.prepend(toc);
        }
    });

});