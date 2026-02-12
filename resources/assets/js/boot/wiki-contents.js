
$(function() {

    let autoId = 1;

    $('.wiki.bbcode').each(function() {
        const $t = $(this);
        const headings = $t.find('> h1, > h2, > h3, > h4, > h5, > h6');
        if (headings.length  > 5) {
            const toc = $('<div/>').addClass('contents');
            const list = $('<ul/>');
            toc.append(list);
            list.append('<li><h2>Contents</h2></li>')

            let hlevelStack = [0];
            for (let i = 0; i < headings.length; i++) {
                const h = $(headings[i]);

                if (!h.attr('id')) h.attr('id', 'wiki-heading-' + autoId++);

                const hlevel = parseInt(h.get(0).tagName.substring(1), 10);
                let tocLevel = hlevelStack.findIndex(hl => hl >= hlevel);
                if (tocLevel === -1) tocLevel = hlevelStack.length;
                else hlevelStack.splice(tocLevel);
                hlevelStack.push(hlevel);
                list.append('<li class="level-' + tocLevel + '"><a href="#' + h.attr('id') + '">' + h.text() + '</a></li>');
            }
            $t.prepend(toc);
        }
    });

});