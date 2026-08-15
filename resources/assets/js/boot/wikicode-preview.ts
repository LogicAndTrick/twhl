import { Dropdown } from 'bootstrap';

import hljs from '../lib/highlight';
import { parser } from '../lib/parser';

/**
 * Cheap jquery replacement
 */
function el<T extends HTMLElement>(type: string, cls?: string, contents?: HTMLElement | string, cback?: (x: T) => void) {
    const element = document.createElement(type) as T;
    if (cls) element.className = cls;
    if (contents) element.append(contents);
    if (cback) cback(element);
    return element;
}

function insertIntoInput(textarea: HTMLTextAreaElement, template: string, cursor: string, cursor2: string, force_newline?: boolean) {
    let val = textarea.value || '',
        st = textarea.selectionStart || 0,
        end = textarea.selectionEnd || 0,
        prev = val.substring(0, st),
        is_newline = prev.length === 0 || prev[prev.length] === '\n',
        before = force_newline === true && !is_newline ? prev + '\n' : prev,
        between = val.substring(st, end),
        curVal = between || cursor,
        after = val.substring(end),
        c1i = template.indexOf('CUR1'),
        c2i = template.indexOf('CUR2'),
        cur = template.replace('CUR1', curVal).replace('CUR2', cursor2);
    textarea.value = before + cur + after;
    textarea.focus();

    if (c2i < 0) c2i = Number.MAX_VALUE;

    let cstart = before.length + c1i + (c2i < c1i ? cursor2.length - 4 : 0),
        cend = cstart + curVal.length;

    if (between && c2i <= val.length) {
        cstart = before.length + c2i + (c2i > c1i ? between.length - 4 : 0);
        cend = cstart + cursor2.length;
    }

    textarea.setSelectionRange(cstart, cend);
    textarea.dispatchEvent(new Event('change', { bubbles: true }));
}

type ButtonDefinition = {
    icon: string;
    text?: string;
    title: string;
    template: string;
    cur1: string;
    cur2: string;
    force_newline?: boolean;
};
type SmileyDefinition = {
    img: string;
    code: string;
};

const buttons: Array<ButtonDefinition[]> = [
    [
        { icon: 'bold', title: 'Bold text', template: '*CUR1*', cur1: 'bold text', cur2: '' },
        { icon: 'italic', title: 'Italic text', template: '/CUR1/', cur1: 'italic text', cur2: '' },
        { icon: 'underline', title: 'Underline text', template: '_CUR1_', cur1: 'underline text', cur2: '' },
        { icon: 'strikethrough', title: 'Strikethrough text', template: '~CUR1~', cur1: 'strikethrough text', cur2: '' },
        { icon: 'code', title: 'Code', template: '`CUR1`', cur1: 'code', cur2: '' },
    ],
    [
        { icon: 'header', text: '1', title: 'Header 1', template: '= CUR1', cur1: 'Header', cur2: '' },
        { icon: 'header', text: '2', title: 'Header 2', template: '== CUR1', cur1: 'Header', cur2: '' },
        { icon: 'header', text: '3', title: 'Header 3', template: '=== CUR1', cur1: 'Header', cur2: '' },
    ],
    [
        { icon: 'link', title: 'Link', template: '[CUR2|CUR1]', cur1: 'link text', cur2: 'http://example.com/' },
        { icon: 'picture-o', title: 'Image', template: '[img:CUR2|CUR1]', cur1: 'caption text', cur2: 'http://example.com/image.jpg' },
        { icon: 'video-camera', title: 'Youtube', template: '[youtube:CUR2|CUR1]', cur1: 'caption text', cur2: 'youtube_id' },
        { icon: 'quote-right', title: 'Quote', template: '> CUR1', cur1: 'quoted text', cur2: '', force_newline: true },
    ],
    [
        { icon: 'list-ul', title: 'Unsorted List', template: '- CUR1', cur1: 'Item 1', cur2: '', force_newline: true },
        { icon: 'list-ol', title: 'Sorted List', template: '# CUR1', cur1: 'Item 1', cur2: '', force_newline: true },
    ],
];
const smilies: SmileyDefinition[] = [
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
    { img: 'nya', code: ':nya:' },
];

function addButtons(container: HTMLElement, textarea: HTMLTextAreaElement) {
    const toolbar = el('div', 'btn-toolbar hidden-xs-only');
    container.append(toolbar);

    for (let j = 0; j < buttons.length; j++) {
        const group = el('div', 'btn-group btn-group-xs me-2');
        toolbar.append(group);
        const a = buttons[j];
        for (let i = 0; i < a.length; i++) {
            const btn = a[i];
            const b = el('button', 'btn btn-outline-inverse btn-xs');
            b.setAttribute('type', 'button');
            b.setAttribute('title', btn.title);
            if (btn.icon) b.append(el('span', 'fa fa-' + btn.icon));
            if (btn.text) b.append(el('span', '', ' ' + btn.text));
            group.append(b);
            b.addEventListener('click', (event) => {
                insertIntoInput(textarea, btn.template, btn.cur1, btn.cur2, btn.force_newline);
                event.preventDefault();
            });
        }
    }

    {
        const ddm = el('div', 'dropdown-menu dropdown-menu-end p-1 smiley-dropdown');
        ddm.style.width = '300px';
        const smiley = el('button', 'btn btn-outline-inverse btn-xs dropdown-toggle');
        smiley.setAttribute('type', 'button');
        smiley.setAttribute('data-bs-toggle', 'dropdown');
        smiley.append(el('span', 'fa fa-smile-o'));

        for (let i = 0; i < smilies.length; i++) {
            const s = smilies[i];
            const sma = el<HTMLAnchorElement>('a', 'btn btn-link btn-xs');
            sma.href = '#';
            sma.setAttribute('title', s.code);
            const img = document.createElement('img');
            img.src = window.urls.images.smiley_folder + '/' + s.img + '.png';
            sma.append(img);
            ddm.append(sma);

            sma.addEventListener('click', (event) => {
                insertIntoInput(textarea, ' ' + s.code + ' CUR1', '', '');
                event.preventDefault();
            });
        }

        const smg = el('div', 'btn-group');
        smg.append(smiley);
        smg.append(ddm);
        toolbar.append(smg);

        new Dropdown(smiley);
    }
}

window.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.wikicode-input').forEach((input) => {
        let group = el('div', 'form-group'),
            heading = el('h4', 'me-auto', 'Message preview'),
            headingCont = el('div', 'd-flex align-items-center'),
            btn = el<HTMLButtonElement>('button', 'btn btn-info btn-xs preview-button', 'Update Preview', (x) => (x.type = 'button')),
            card = el('div', 'card'),
            panel = el('div', 'card-body bbcode'),
            form = input.closest('form')!,
            ta = input.querySelector('textarea')!,
            name = ta.getAttribute('name')!,
            help = el<HTMLAnchorElement>('a', 'pull-right', 'Formatting help', (x) => {
                x.target = '_blank';
                x.href = window.urls.wiki.formatting_guide;
            }),
            btnCon = el('div', 'mb-1'),
            livePreviewInput = el<HTMLInputElement>('input', 'form-check-input', undefined, (x) => {
                x.type = 'checkbox';
                x.checked = !document.cookie.split(';').some((x) => x.includes('live_preview=no'));
            }),
            livePreviewLabel = el('label', 'form-check ms-3', 'Live preview'),
            fullscreen = el<HTMLAnchorElement>('a', 'ms-2 hidden-sm-down', '', (x) => {
                x.href = '#';
                x.innerHTML = '<span class="fa fa-arrows-alt"></span> Full screen editor';
            });

        livePreviewLabel.prepend(livePreviewInput);
        headingCont.append(heading);
        headingCont.append(btn);
        headingCont.append(livePreviewLabel);
        card.append(panel);
        group.append(headingCont, card);
        input.append(group);
        ta.parentElement!.prepend(help);

        ta.before(btnCon);
        addButtons(btnCon, ta);

        const tb = btnCon.querySelector('.btn-toolbar')!;
        const fsbDiv = el('div');
        fsbDiv.append(fullscreen);
        tb.append(fsbDiv);
        fullscreen.addEventListener('click', (event) => {
            event.preventDefault();
            input.classList.toggle('full-screen-wikicode-editor');
            ta.dispatchEvent(new Event('change'));
        });

        const refresh = async function () {
            const formData = new FormData(form);
            let text = formData.get(name)!.toString();
            text = text.replace(/^\w/gim, function (match, index) {
                return '\x02' + index + '\x03' + match;
            });

            const result = parser.ParseResult(text);
            const data = result.ToHtml();

            const event = new CustomEvent('bbcode-preview-updating', {
                detail: { html: data, element: panel },
            });
            ta.dispatchEvent(event);
            panel.innerHTML = await Promise.resolve(event.detail.html);
            panel.querySelectorAll('pre code').forEach((x) => {
                hljs.highlightElement(x as HTMLElement);
            });
            ta.dispatchEvent(
                new CustomEvent('bbcode-preview-updated', {
                    detail: { element: panel },
                }),
            );

            const active = document.activeElement as HTMLTextAreaElement;
            if (active && active === ta && input.classList.contains('full-screen-wikicode-editor')) {
                const idx = active.selectionStart;
                let marker = null;
                for (const el of panel.querySelectorAll('[data-position]')) {
                    const pos = parseInt(el.getAttribute('data-position')!, 10);
                    if (pos > idx) break;
                    marker = el;
                }
                if (marker) {
                    marker.classList.add('current-cursor');
                    marker.scrollIntoView({ block: 'center' });
                }
            }
        };

        btn.addEventListener('click', refresh);

        let timeout: number | undefined = undefined;
        const liveRefresh = function () {
            clearTimeout(timeout);
            if (!livePreviewInput.checked) return;
            timeout = setTimeout(() => {
                refresh();
            }, 250);
        };
        ta.addEventListener('input', liveRefresh);
        ta.addEventListener('change', liveRefresh);
        document.addEventListener('selectionchange', liveRefresh);
        livePreviewInput.addEventListener('change', () => {
            btn.classList.toggle('d-none', livePreviewInput.checked);
            document.cookie = `live_preview=${livePreviewInput.checked ? 'yes' : 'no'}; expires=Fri, 31 Dec 9999 23:59:59 GMT;`;
            liveRefresh();
        });

        refresh();
        btn.classList.toggle('d-none', livePreviewInput.checked);
    });

    const imageTypesAcceptedServerSide = new Set(['image/avif', 'image/gif', 'image/jpeg', 'image/png', 'image/webp']);

    // Note: When changing `maxImageUploadSize` you need to also change the `max:` value in
    // `ApiController.php` post_image_upload()'s validation rules to the same value divided by 1024
    const maxImageUploadSize = 2048 * 1024;
    // Note: When changing `maxImageUploadWidth` or `maxImageUploadHeight` you need to also change
    // `max_width` and `max_height` in `ApiController.php` post_image_upload()'s validation rules to
    // the same values
    const maxImageUploadWidth = 2000;
    const maxImageUploadHeight = 2000;

    const findImageFileInClipboard = (clipboardData: DataTransfer | null) => {
        const items = clipboardData && clipboardData.items ? Array.from(clipboardData.items) : [];
        const item = items.find(({ kind, type }) => kind === 'file' && type.startsWith('image/'));
        return item ? item.getAsFile() : undefined;
    };

    const isOpaque = (canvas: OffscreenCanvas) => {
        const rgbaData = canvas.getContext('2d')!.getImageData(0, 0, canvas.width, canvas.height).data;
        for (let dataIndex = 3; dataIndex < rgbaData.length; dataIndex += 4) {
            if (rgbaData[dataIndex] < 255) {
                return false;
            }
        }
        return true;
    };

    const compressImageFromCanvas = async (canvas: OffscreenCanvas, quality: number) => {
        const compressedBlob = await canvas.convertToBlob({
            quality,
            type: quality === 1 ? 'image/png' : 'image/jpeg',
        });
        return new File([compressedBlob], 'image', { type: compressedBlob.type });
    };

    const renderBitmapScaled = (original: ImageBitmap, scale: number) => {
        const resized = new OffscreenCanvas(Math.round(original.width * scale) || 1, Math.round(original.height * scale) || 1);
        const context = resized.getContext('2d')!;
        context.imageSmoothingQuality = 'high';
        context.drawImage(original, 0, 0, resized.width, resized.height);
        return resized;
    };

    const recompressImageIfNeeded = async (imageFile: File): Promise<File | null> => {
        const fileTooLarge = imageFile.size > maxImageUploadSize;
        const unsupportedImageFormat = !imageTypesAcceptedServerSide.has(imageFile.type);
        const bitmap = await createImageBitmap(imageFile, { premultiplyAlpha: 'none' }).catch((error) => {
            console.error('Image loading failed. Original error: %o', error);
            throw new Error('Image loading failed. Your image file may be corrupted or in an unsupported file format');
        });
        // Images with overly large dimensions need to be scaled down
        let scale = Math.min(1, maxImageUploadWidth / bitmap.width, maxImageUploadHeight / bitmap.height);
        if (scale === 1 && !fileTooLarge && !unsupportedImageFormat) {
            // No need to recompress. The dimensions, file size, and file format are supported by the API endpoint
            return imageFile;
        }
        let scaledCanvas = renderBitmapScaled(bitmap, scale);
        if (isOpaque(scaledCanvas)) {
            // An opaque image is - if necessary - compressed with JPEG,
            // at lower and lower quality until it's small enough
            for (let quality = 1; quality >= 0.05; quality -= 0.05) {
                let newFile = await compressImageFromCanvas(scaledCanvas, quality);
                if (newFile.size <= maxImageUploadSize) {
                    return newFile;
                }
            }
        } else {
            // A transparent image is compressed with PNG, if necessary with reduced dimensions until it's small enough
            while (scale >= 1 / maxImageUploadWidth) {
                let newFile = await compressImageFromCanvas(scaledCanvas, 1);
                if (newFile.size <= maxImageUploadSize) {
                    return newFile;
                }
                scale *= 0.8;
                scaledCanvas = renderBitmapScaled(bitmap, scale);
            }
        }

        throw new Error('The image file is too large and could not be compressed enough to allow an upload');
    };

    document.addEventListener('paste', async (event) => {
        const active = document.activeElement as HTMLTextAreaElement;
        if (!active || !active.closest('.wikicode-input')) return;

        let imageFile = findImageFileInClipboard(event.clipboardData);
        if (!imageFile) return;
        event.preventDefault();

        const id = Date.now();
        const tempText = 'uploading image ' + id + '...';
        insertIntoInput(active, '[img:' + tempText + ']', '', '', true);

        let replace;

        try {
            imageFile = await recompressImageIfNeeded(imageFile);
        } catch (error: any) {
            replace = `Error: ${error.message}`;
        }
        if (imageFile) {
            const form = new FormData();
            form.append('image', imageFile);
            const response = await fetch(window.urls.api.image_upload, { method: 'post', body: form });
            const json = await response.json();

            if (!response.ok) {
                replace = 'Error: ' + json.image[0];
            } else {
                replace = json.url;
            }
        }

        let text = active.value;
        if (text.indexOf(tempText) >= 0) {
            text = text.replace(tempText, replace);
        } else {
            text += '\n[img:' + replace + ']';
        }
        active.value = text;
    });
});

const promptWhenClosing = function (form: HTMLFormElement) {
    const names = ['title', 'file', 'content_text'];
    let changed = new Set();

    const promptBeforeUnloadListener = (event: Event) => {
        if (changed.size > 0) {
            event.preventDefault();
        }
    };

    const watch = function (input: HTMLInputElement | HTMLTextAreaElement) {
        const initial = input.value || '';
        input.addEventListener('input', () => {
            const current = input.value || '';
            if (initial === current) changed.delete(input);
            else changed.add(input);
        });
    };

    for (const name of names) {
        const el = form.querySelector(`[name="${name}"]`);
        if (el) watch(el as HTMLInputElement | HTMLTextAreaElement);
    }

    window.addEventListener('beforeunload', promptBeforeUnloadListener);

    // Remove the prompt if submitting the form
    form.addEventListener('submit', () => {
        window.removeEventListener('beforeunload', promptBeforeUnloadListener);
    });
};

Object.assign(window, { promptWhenClosing });
