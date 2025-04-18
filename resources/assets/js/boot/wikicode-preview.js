const parser = window.parser;

function insertIntoInput(textarea, template, cursor, cursor2, force_newline) {
    var val = textarea.val() || '',
        st = textarea[0].selectionStart || 0,
        end = textarea[0].selectionEnd || 0,
        prev = val.substr(0, st),
        is_newline = prev.length === 0 || prev[prev.length] === '\n',
        before = force_newline === true && !is_newline ? prev + '\n' : prev,
        between = val.substring(st, end),
        curVal = between || cursor,
        after = val.substr(end),
        c1i = template.indexOf('CUR1'),
        c2i = template.indexOf('CUR2'),
        cur = template.replace('CUR1', curVal).replace('CUR2', cursor2),
        newVal = before + cur + after;
    textarea.val(newVal).focus();

    if (c2i < 0) c2i = Number.MAX_VALUE;

    var cstart = before.length + c1i + (c2i < c1i ? cursor2.length - 4 : 0),
        cend = cstart + curVal.length;

    if (between && c2i <= val.length) {
        cstart = before.length + c2i + (c2i > c1i ? between.length - 4 : 0);
        cend = cstart + cursor2.length;
    }

    textarea[0].setSelectionRange(cstart, cend);
    textarea[0].dispatchEvent(new Event('change', { bubbles: true }))
}

// noinspection JSAnnotator
var buttons = [
    [
        { icon: 'bold', title: 'Bold text', template: '*CUR1*', cur1: 'bold text', cur2: '' },
        { icon: 'italic', title: 'Italic text', template: '/CUR1/', cur1: 'italic text', cur2: '' },
        { icon: 'underline', title: 'Underline text', template: '_CUR1_', cur1: 'underline text', cur2: '' },
        { icon: 'strikethrough', title: 'Strikethrough text', template: '~CUR1~', cur1: 'strikethrough text', cur2: '' },
        { icon: 'code', title: 'Code', template: '`CUR1`', cur1: 'code', cur2: '' },
    ], [
        { icon: 'header', text: '1', title: 'Header 1', template: '= CUR1', cur1: 'Header', cur2: '' },
        { icon: 'header', text: '2', title: 'Header 2', template: '== CUR1', cur1: 'Header', cur2: '' },
        { icon: 'header', text: '3', title: 'Header 3', template: '=== CUR1', cur1: 'Header', cur2: '' },
    ], [
        { icon: 'link', title: 'Link', template: '[CUR2|CUR1]', cur1: 'link text', cur2: 'http://example.com/' },
        { icon: 'picture-o', title: 'Image', template: '[img:CUR2|CUR1]', cur1: 'caption text', cur2: 'http://example.com/image.jpg' },
        { icon: 'video-camera', title: 'Youtube', template: '[youtube:CUR2|CUR1]', cur1: 'caption text', cur2: 'youtube_id' },
        { icon: 'quote-right', title: 'Quote', template: '> CUR1', cur1: 'quoted text', cur2: '', force_newline: true },
    ], [
        { icon: 'list-ul', title: 'Unsorted List', template: '- CUR1', cur1: 'Item 1', cur2: '', force_newline: true },
        { icon: 'list-ol', title: 'Sorted List', template: '# CUR1', cur1: 'Item 1', cur2: '', force_newline: true },
    ]
];
var smilies = [
    { img: 'aggrieved', code: ':aggrieved:' },
    { img: 'aghast', code: ':aghast:' },
    { img: 'angry', code: ':x' },
    { img: 'badass', code: ':badass:' },
    { img: 'confused', code: ':confused:' },
    { img: 'cry', code: ':cry:' },
    { img: 'cyclops', code: ':cyclops:' },
    { img: 'lol', code: ':lol:' },
    { img: 'frown', code: ':|' },
    { img: 'furious', code: ':furious:' },
    { img: 'glad', code: ':glad:' },
    { img: 'heart', code: ':heart:' },
    { img: 'grin', code: ':D' },
    { img: 'nervous', code: ':nervous:' },
    { img: 'nuke', code: ':nuke:' },
    { img: 'nuts', code: ':nuts:' },
    { img: 'quizzical', code: ':quizzical:' },
    { img: 'rollseyes', code: ':roll:' },
    { img: 'sad', code: ':(' },
    { img: 'smile', code: ':)' },
    { img: 'surprised', code: ':o' },
    { img: 'thebox', code: ':thebox:' },
    { img: 'thefinger', code: ':thefinger:' },
    { img: 'tired', code: ':tired:' },
    { img: 'tongue', code: ':P' },
    { img: 'toocool', code: ':cool:' },
    { img: 'unsure', code: ':\\' },
    { img: 'biggrin', code: ':biggrin:' },
    { img: 'wink', code: ';)' },
    { img: 'zonked', code: ':zonked:' },
    { img: 'sarcastic', code: ':sarcastic:' },
    { img: 'combine', code: ':combine:' },
    { img: 'gak', code: ':gak:' },
    { img: 'animehappy', code: ':^_^:' },
    { img: 'pwnt', code: ':pwned:' },
    { img: 'target', code: ':target:' },
    { img: 'ninja', code: ':ninja:' },
    { img: 'hammer', code: ':hammer:' },
    { img: 'pirate', code: ':pirate:' },
    { img: 'walter', code: ':walter:' },
    { img: 'plastered', code: ':plastered:' },
    { img: 'bigmouth', code: ':zomg:' },
    { img: 'brokenheart', code: ':heartbreak:' },
    { img: 'ciggiesmilie', code: ':ciggie:' },
    { img: 'combines', code: ':combines:' },
    { img: 'crowbar', code: ':crowbar:' },
    { img: 'death', code: ':death:' },
    { img: 'freeman', code: ':freeman:' },
    { img: 'hecu', code: ':hecu:' },
    { img: 'nya', code: ':nya:' }
];

function addButtons(container, textarea) {

    var toolbar = $('<div class="btn-toolbar hidden-xs-only"></div>').appendTo(container);

    for (var j = 0; j < buttons.length; j++) {
        var group = $('<div class="btn-group btn-group-xs me-2"></div>').appendTo(toolbar);
        var a = buttons[j];
        for (var i = 0; i < a.length; i++) {
            var btn = a[i];
            var b = $('<button type="button" class="btn btn-outline-inverse btn-xs"></button>');
            b.attr('title', btn.title);
            if (btn.icon) b.append($('<span></span>').addClass('fa fa-' + btn.icon));
            if (btn.text) b.append($('<span></span>').text(' ' + btn.text));
            group.append(b);
            b.on('click', insertIntoInput.bind(window, textarea, btn.template, btn.cur1, btn.cur2, btn.force_newline));
        }
    }

    var ddm = $('<div class="dropdown-menu dropdown-menu-end p-1 smiley-dropdown" style="width: 300px;"></div>');
    var smiley = $('<button type="button" class="btn btn-outline-inverse btn-xs dropdown-toggle" data-bs-toggle="dropdown"></button>');
    smiley.append('<span class="fa fa-smile-o"></span>');

    for (i = 0; i < smilies.length; i++) {
        var s = smilies[i];
        var sma = $('<a href="#" class="btn btn-link btn-xs" title="' + s.code + '"><img src="' + window.urls.images.smiley_folder + '/' + s.img + '.png" /></a>');
        ddm.append(sma);

        sma.on('click', function(event) {
            event.preventDefault();
            insertIntoInput(textarea, ' ' + $(event.currentTarget).attr('title') + ' CUR1', '', '');
        });
    }

    var smg = $('<div class="btn-group"></div>').appendTo(toolbar);
    smg.append(smiley).append(ddm);

    smiley.dropdown();
}

$(function() {
    $('.wikicode-input').each(function() {
        var $t = $(this),
            group = $('<div class="form-group"></div>'),
            heading = $('<h4>Message preview</h4>)'),
            btn = $('<button type="button" class="btn btn-info btn-xs preview-button">Update Preview</button>'),
            card = $('<div class="card"></div>'),
            panel = $('<div class="card-body bbcode"></div>'),
            form = $t.closest('form'),
            ta = $t.find('textarea'),
            name = ta.attr('name'),
            help = $('<a class="pull-right" target="_blank" href="' + window.urls.wiki.formatting_guide + '">Formatting help</a>'),
            fullscreen = $('<a href="#" class="ms-2 hidden-sm-down"><span class="fa fa-arrows-alt"></span> Full screen editor</a>'),
            btnCon = $('<div class="mb-1"></div>');
        heading.append(btn);
        card.append(panel);
        group.append(heading).append(card);
        $t.append(group);
        ta.parent().prepend(help);

        ta.before(btnCon);
        addButtons(btnCon, ta);

        const tb = btnCon.find('.btn-toolbar');
        tb.append($('<div></div>').append(fullscreen));
        fullscreen.click(event => {
            event.preventDefault();
            $t.toggleClass('full-screen-wikicode-editor');
            ta.trigger('change');
        });

        const refresh = async function() {
            const formData = new FormData(form[0]);
            let text = formData.get(name);
            text = text.replace(/^\w/img, function(match, index) {
                return "\2" + index + "\3" + match;
            });

            // do some string replace stuffs
            const result = window.parser.ParseResult(text);
            const data = result.ToHtml();

            const event = new CustomEvent('bbcode-preview-updating', {
                detail: { html: data, element: panel[0] }
            });
            ta[0].dispatchEvent(event);
            panel[0].innerHTML = await Promise.resolve(event.detail.html);
            panel[0].querySelectorAll('pre code').forEach(x => {
                hljs.highlightBlock(x);
            });
            ta[0].dispatchEvent(new CustomEvent('bbcode-preview-updated', {
                detail: { element: panel[0] }
            }));

            const active = document.activeElement;
            if (active && active === ta[0] && $t[0].classList.contains('full-screen-wikicode-editor')) {
                const idx = active.selectionStart;
                let marker = null;
                for (const el of panel[0].querySelectorAll('[data-position]')) {
                    const pos = parseInt(el.getAttribute('data-position'), 10);
                    if (pos > idx) break;
                    marker = el;
                }
                if (marker) {
                    marker.classList.add('current-cursor');
                    marker.scrollIntoView({block: "center"});
                }
            }
        };

        btn.on('click', refresh);

        const throttledRefresh = $.throttle(250, () => {
            if (!$t.hasClass('full-screen-wikicode-editor')) return;
            refresh();
        });
        ta.on('input change', throttledRefresh);
        document.addEventListener('selectionchange', throttledRefresh);
    });

    const imageTypeToExtension = {
        "image/gif": "gif",
        "image/jpeg": "jpg",
        "image/png": "png",
    };

    const findImageClipboardData = (clipboardData) => {
        for (const item of Array.from(clipboardData?.items ?? [])) {
            const { kind, type } = item;
            if (kind !== 'file' || !(type in imageTypeToExtension)) continue;

            const fileData = item.getAsFile();
            if (fileData) { 
                return {
                    fileData,
                    fileExtension: imageTypeToExtension[type],
                }
            }
        }
    }

    document.addEventListener('paste', async event => {
        const active = document.activeElement;
        if (!active || !$(active).closest('.wikicode-input').length) return;

        let imageData = findImageClipboardData(event.clipboardData);
        if (!imageData) return;
        const { fileData, fileExtension } = imageData;

        event.preventDefault();

        const form = new FormData();
        form.append('image', fileData, `image.${fileExtension}`);

        const $t = $(active);
        const id = Date.now();

        const tempText = 'uploading image ' + id + '...';
        insertIntoInput($t, '[img:' + tempText + ']', '', '', true);

        const response = await fetch(window.urls.api.image_upload, { method: 'post', body: form });
        const json = await response.json();

        let replace;

        if (!response.ok) {
            replace = 'Error: ' + json.image[0];
        } else {
            replace = json.url;
        }
        let text = $t.val();
        if (text.indexOf(tempText) >= 0) {
            text = text.replace(tempText, replace);
        } else {
            text += '\n[img:' + replace + ']';
        }
        $t.val(text);
    });
});

const promptWhenClosing = function (form) {
    const names = ['title', 'file', 'content_text'];
    let changed = new Set();

    const promptBeforeUnloadListener = event => {
        if (changed.size > 0) {
            event.preventDefault();
            event.returnValue = "Your changes to the page have not been saved. Close anyway?";
        }
    };

    const watch = function (input) {
        const initial = input.value || '';
        input.addEventListener('input', () => {
            const current = input.value || '';
            if (initial === current) changed.delete(input);
            else changed.add(input);
        });
    };

    for (const name of names) {
        const el = form.querySelector(`[name="${name}"]`);
        if (el) watch(el);
    }

    window.addEventListener('beforeunload', promptBeforeUnloadListener);

    // Remove the prompt if submitting the form
    form.addEventListener('submit', () => {
        window.removeEventListener('beforeunload', promptBeforeUnloadListener);
    });
};