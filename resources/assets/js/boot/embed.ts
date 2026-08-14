import { nanoTemplate } from '../lib/nano-templating';
import { readableTime } from '../lib/utils';

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

const embed_templates = {
    vault_slider_screenshot: `<div class="swiper-slide"><img src="{image_large}" alt="Screenshot" /></div>`,
    vault_slider_thumbnail: `<div class="swiper-slide"><img src="{image_thumb}" alt="Thumbnail" /></div>`,
    vault_slider: `
    <div id="{slider_id}" class="swiper">
        <div class="swiper-wrapper">{slider_screenshots}</div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
    </div>
    <div id="{slider_id}-thumbnails" class="swiper swiper-thumbs">
        <div class="swiper-wrapper">{slider_thumbnails}</div>
    </div>
    `.trim(),
    vault_slider_empty: `<div class="empty-swiper">{slider_screenshots}</div>`,
    vault: `
    <div class="slot">
        <div class="slot-heading mb-3">
            <div class="pull-right text-center" title="{game_name}">
                <img class="game-icon" src="{game_image}" alt="{game_name}">
                <small class="d-block">{game_abbr}</small>
            </div>
            <div class="slot-avatar">
                <span class="avatar small" title="{user_name}">
                    <a href="{user_url}"><img src="{user_avatar}" alt="{user_name}"></a>
                </span>
            </div>
            <div class="slot-title">
                <a href="{url}">{name}</a> by <a href="{user_url}">{user_name}</a>
            </div>
            <div class="slot-subtitle">Posted {created} &bull; {category} &bull; {game_name}</div>
        </div>
        <div class="slot-main">
            {vault_slider}
        </div>
    </div>
    `.trim(),
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
    vault_screenshots: {
        image_large: string;
        image_thumb: string;
        is_primary: boolean;
        order_index: number;
    }[];
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

        const shots = images.map((s) => nanoTemplate(embed_templates.vault_slider_screenshot, s)).join('');
        const thumbs = images.map((s) => nanoTemplate(embed_templates.vault_slider_thumbnail, s)).join('');

        const sid = 'vault-slider-' + __uniq++;
        const slider_template = images.length > 1 ? embed_templates.vault_slider : embed_templates.vault_slider_empty;

        element.innerHTML = nanoTemplate(embed_templates.vault, {
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
            vault_slider: nanoTemplate(slider_template, {
                slider_id: sid,
                slider_screenshots: shots,
                slider_thumbnails: thumbs,
            }),
        });

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
