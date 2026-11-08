import { nanoTemplate } from '../lib/nano-templating';
import { elem, readableTime } from '../lib/utils';

type VaultScreenshot = {
    image_large: string;
    image_thumb: string;
    is_primary: boolean;
    order_index: number;
};

type VaultItem = {
    name: string;
    updated_at: string;
    game: {
        name: string;
        abbreviation: string;
    };
    user: {
        name: string;
        avatar_small: string;
    };
    vault_category: {
        name: string;
    };
    vault_screenshots: VaultScreenshot[];
};

document.addEventListener('click', (event) => {
    const target = (event.target as HTMLElement).closest('.video-content .uninitialised') as HTMLElement;
    if (!target) return;

    const ytid = target.dataset.youtubeId;
    if (!ytid) return;

    const frame = document.createElement('iframe');
    frame.allowFullscreen = false;
    frame.src = 'https://www.youtube.com/embed/' + ytid + '?autoplay=1&rel=0';
    frame.frameBorder = '0';
    frame.referrerPolicy = 'strict-origin-when-cross-origin';
    frame.classList.add('caption-body');
    target.replaceWith(frame);
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.caption-panel').forEach((x) => {
        x.setAttribute('tabindex', '-1');
    });
});

document.addEventListener('click', (event) => {
    const panel = (event.target as HTMLElement).closest('.caption-panel') as HTMLElement;
    if (panel) panel.focus();
});

type VaultSliderData = {
    slider_id: string;
    slider_screenshots: HTMLElement[];
    slider_thumbnails: HTMLElement[];
};

type VaultEmbedTemplateData = {
    url: string;
    name: string;
    created: string;
    category: string;
    game_abbr: string;
    game_name: string;
    game_image: string;
    user_url: string;
    user_avatar: string;
    user_name: string;
    vault_slider: HTMLElement[];
};

const embed_templates = {
    vault_slider_screenshot({ image_large }: VaultScreenshot) {
        const img = elem('img', undefined, undefined, (i) => {
            i.src = image_large;
            i.alt = 'Screenshot';
        });
        return elem('div', 'swiper-slide', img);
    },
    vault_slider_thumbnail({ image_thumb }: VaultScreenshot) {
        const img = elem('img', undefined, undefined, (i) => {
            i.src = image_thumb;
            i.alt = 'Thumbnail';
        });
        return elem('div', 'swiper-slide', img);
    },
    vault_slider({ slider_id, slider_screenshots, slider_thumbnails }: VaultSliderData): HTMLElement[] {
        const imgWrapper = elem('div', 'swiper-wrapper', slider_screenshots);
        const thumbWrapper = elem('div', 'swiper-wrapper', slider_thumbnails);
        const prev = elem('div', 'swiper-button-prev');
        const next = elem('div', 'swiper-button-next');
        const top = elem('div', 'swiper', [imgWrapper, prev, next], (d) => (d.id = slider_id));
        const btm = elem('div', 'swiper swiper-thumbs', thumbWrapper, (d) => (d.id = slider_id + '-thumbnails'));
        return [top, btm];
    },
    vault_slider_empty({ slider_screenshots }: VaultSliderData): HTMLElement[] {
        return [elem('div', 'empty-swiper', slider_screenshots)];
    },
    vault({ url, name, created, category, game_abbr, game_name, game_image, user_url, user_avatar, user_name, vault_slider }: VaultEmbedTemplateData) {
        const gameIcon = elem('img', 'game-icon', undefined, (d) => ([d.src, d.alt] = [game_image, game_name]));
        const gameAbbr = elem('small', 'd-block', game_abbr);
        const game = elem('div', 'slot-end text-center', [gameIcon, gameAbbr], (d) => (d.title = game_name));

        const avatarImg = elem('img', undefined, undefined, (d) => ([d.src, d.alt] = [user_avatar, user_name]));
        const avatarUrl = elem('a', undefined, avatarImg, (d) => (d.href = user_url));
        const avatarSpan = elem('span', 'avatar small', avatarUrl, (d) => (d.title = user_name));
        const avatar = elem('div', 'slot-avatar', avatarSpan);

        const titleUrl = elem('a', undefined, name, (d) => (d.href = url));
        const byText = elem('span', undefined, ' by ');
        const userUrl = elem('a', undefined, user_name, (d) => (d.href = user_url));
        const title = elem('div', 'slot-title', [titleUrl, byText, userUrl]);

        const subtitle = elem('div', 'slot-subtitle', `Posted ${created} • ${category} • ${game_name}`);

        const heading = elem('div', 'slot-heading mb-3', [game, avatar, title, subtitle]);
        const main = elem('div', 'slot-main', vault_slider);
        return elem('div', 'slot', [heading, main]);
    },
};

let __uniq = 0;
const embed_callbacks = {
    vault(element: HTMLElement, data: VaultItem) {
        const images = data.vault_screenshots || [];
        images.forEach((img) => {
            img.image_large = nanoTemplate(window.urls.embed.vault_screenshot, { shot: img.image_large });
            img.image_thumb = nanoTemplate(window.urls.embed.vault_screenshot, { shot: img.image_thumb });
        });

        if (!images.length) {
            images.push({
                image_large: window.urls.images.no_screenshot_640,
                image_thumb: window.urls.images.no_screenshot_320,
                order_index: 0,
                is_primary: true,
            });
        }

        images.sort((a, b) => {
            if (a.is_primary) return -1;
            if (b.is_primary) return +1;
            return a.order_index - b.order_index;
        });

        const shots = images.map((s) => embed_templates.vault_slider_screenshot(s));
        const thumbs = images.map((s) => embed_templates.vault_slider_thumbnail(s));

        const sid = 'vault-slider-' + __uniq++;
        const slider_template = images.length > 1 ? embed_templates.vault_slider : embed_templates.vault_slider_empty;

        const elem = embed_templates.vault({
            url: nanoTemplate(window.urls.view.vault, data),
            name: data.name,
            user_url: nanoTemplate(window.urls.view.user, data.user),
            user_avatar: data.user.avatar_small,
            user_name: data.user.name,
            created: readableTime(Date.parse(data.updated_at)),
            category: data.vault_category.name,
            game_abbr: data.game.abbreviation,
            game_name: data.game.name,
            game_image: nanoTemplate(window.urls.embed.game_icon, { game_abbr: data.game.abbreviation }),
            vault_slider: slider_template({
                slider_id: sid,
                slider_screenshots: shots,
                slider_thumbnails: thumbs,
            }),
        });
        element.replaceChildren(elem);

        const swiperEl = document.getElementById(sid);
        const thumbsEl = document.getElementById(sid + '-thumbnails');
        if (swiperEl && swiperEl.classList.contains('swiper')) {
            (window as any).initialiseSwiper({
                element: swiperEl,
                thumbnailsElement: thumbsEl,
            });
        }
    },
};

document.addEventListener('DOMContentLoaded', () => {
    const obs = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;

            const el = entry.target as HTMLElement;
            el.textContent = 'Loading...';

            const par = el.parentElement,
                typ = el.dataset['embedType'] as keyof typeof embed_callbacks,
                id = el.dataset[typ + 'Id'],
                url = window.urls.embed[typ];
            obs.unobserve(el);
            if (!par) return;

            fetch(url + '?id=' + id + '&expand=user,vault_screenshots,game,vault_category')
                .then((resp) => resp.json())
                .then((data) => embed_callbacks[typ].call(window, par!, data[0]));
        });
    });
    document.querySelectorAll('.embed-content .uninitialised').forEach((el) => obs.observe(el));
});
