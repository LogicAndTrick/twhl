
document.addEventListener('DOMContentLoaded', () => {

    let autoId = 1;

    document.querySelectorAll('.wiki.bbcode').forEach(el => {
        const headings = el.querySelectorAll(':scope > h1, :scope > h2, :scope > h3, :scope > h4, :scope > h5, :scope > h6');

        if (headings.length  > 5) {
            const toc = document.createElement('div');
            toc.className = 'contents';

            const list = document.createElement('ul');

            const conHeader = document.createElement('h2');
            conHeader.textContent = 'Contents';

            const conLi = document.createElement('li');
            conLi.append(conHeader);

            toc.append(list);
            list.append(conLi);

            const hlevelStack = [0];
            for (let i = 0; i < headings.length; i++) {
                const h = headings[i];
                if (!h.id) h.id = 'wiki-heading-' + autoId++;

                const hlevel = parseInt(h.tagName.substring(1), 10);
                let tocLevel = hlevelStack.findIndex(hl => hl >= hlevel);
                if (tocLevel === -1) tocLevel = hlevelStack.length;
                hlevelStack.splice(tocLevel);
                hlevelStack.push(hlevel);

                const el = document.createElement('li');
                el.className = 'level-' + tocLevel;

                const a = document.createElement('a');
                a.href = '#' + h.id;
                a.textContent = h.textContent
                el.append(a);

                list.append(el);

                // add a hyperlink to the heading id
                const icon = document.createElement('span');
                icon.className = 'fa fa-link';

                const link = document.createElement('a');
                link.href = '#' + h.id;
                link.className = 'anchor-link';

                link.append(icon);
                h.append(link);
            }
            el.prepend(toc);
        }
    });

});