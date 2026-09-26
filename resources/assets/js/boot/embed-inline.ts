import { formatFilesize } from '../lib/utils';

type Revision = {
    title: string;
};
type Page = {
    exists: boolean;
    revision: Revision;
    slug: string;
};
type Embed = {
    slug: string;
    exists: boolean;
    revision: Revision;
    upload: {};
    meta: Array<any>;
};
type PagesAndEmbeds = {
    pages: Record<string, Page>;
    embeds: Record<string, Embed>;
};

async function loadPagesAndEmbeds(c: HTMLElement, pages: string[], embeds: string[]) {
    const resp = await fetch('/api/wiki-objects/page-information', {
        method: 'post',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken,
        },
        body: JSON.stringify({ pages, embeds }),
    });
    if (!resp.ok) return;

    const data: PagesAndEmbeds = await resp.json();

    for (const po in data.pages) {
        const p = data.pages[po];
        const links = Array.from(c.querySelectorAll('a[href*="://' + window.location.host + '/wiki/page/"]')) as HTMLAnchorElement[];
        const pl = links.filter((x) => {
            const hr = decodeURIComponent(x.href);
            const uc = '://' + window.location.host + '/wiki/page/' + p.slug;
            return hr && hr.toLowerCase().endsWith(uc.toLowerCase());
        });
        if (!p.exists) {
            pl.forEach((x) => {
                x.classList.add('text-danger');
                x.title = 'Page does not exist yet - click to create it';
            });
        } else {
            pl.forEach((x) => {
                x.title = p.revision.title;
            });
        }
    }
    for (const eo in data.embeds) {
        const e = data.embeds[eo];
        const links = Array.from(c.querySelectorAll('a[href*="://' + window.location.host + '/wiki/embed/"]')) as HTMLAnchorElement[];
        const pl = links.filter((x) => {
            const hr = decodeURIComponent(x.href);
            const uc = '://' + window.location.host + '/wiki/embed/' + e.slug;
            return hr && hr.toLowerCase().indexOf(uc.toLowerCase()) >= 0;
        });
        if (!e.exists) {
            pl.forEach((x) => {
                x.classList.add('text-danger');
                x.title = 'File does not exist';
            });
        } else {
            const sz = e.meta.filter((m: any) => m.key === 's');
            const size = sz && sz.length > 0 && parseInt(sz[0].value, 10);
            const txt = size ? '(' + formatFilesize(size) + ')' : '(Unknown size)';
            pl.forEach((x) => {
                const span = document.createElement('span');
                span.textContent = txt;
                x.append(span);
            });
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const allC = document.querySelectorAll('.wiki.bbcode');
    if (allC.length === 1) {
        const c = allC[0] as HTMLElement;

        const allLinks = Array.from(c.querySelectorAll('a[href*="://' + window.location.host + '/wiki/page/"]'))
            .map((x) => {
                if (!(x instanceof HTMLAnchorElement)) return '';
                let url = x.href;
                if (url.indexOf('#') >= 0) {
                    const spl = url.split('#');
                    url = spl[0];
                }
                return decodeURIComponent(url.replace(/^.*\//gi, ''));
            })
            .filter((x) => {
                return !x.match(/^category:/gi);
            });
        const pages = Array.from(new Set(allLinks));

        const allEmbeds = Array.from(c.querySelectorAll('.embedded-inline.download a'))
            .map((x) => {
                if (!(x instanceof HTMLAnchorElement)) return '';
                return decodeURIComponent(x.href.replace(/^.*\//gi, ''));
            })
            .filter((x) => x != '');
        const embeds = Array.from(new Set(allEmbeds));
        if (pages.length > 0 || embeds.length > 0) {
            loadPagesAndEmbeds(c, pages, embeds);
        }
    }

    const autoPlayVideos = document.querySelectorAll('video[autoplay]') as NodeListOf<HTMLVideoElement>;
    autoPlayVideos.forEach((t) => {
        if (!t.paused) return;

        const ol = document.createElement('div');
        ol.classList.add('autoplay-overlay');
        ol.textContent = 'Video - Click to play';
        t.parentNode?.insertBefore(ol, t);

        t.addEventListener(
            'play',
            () => {
                t.parentNode?.querySelectorAll('.autoplay-overlay').forEach((x) => x.remove());
            },
            { once: true },
        );
    });
});
