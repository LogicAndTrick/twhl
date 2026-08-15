import { Swiper } from 'swiper/bundle';

type InitSwiperOptions = {
    element: HTMLElement;
    thumbnailsElement: HTMLElement | null;
};

(window as any).initialiseSwiper = function (options: InitSwiperOptions) {
    let thumbs: Swiper | null = null;
    if (options.thumbnailsElement) {
        const numThumbs = options.thumbnailsElement.querySelectorAll('.swiper-slide').length;
        thumbs = new Swiper(options.thumbnailsElement, {
            loop: numThumbs >= 4,
            spaceBetween: 5,
            slidesPerView: 4, // xs
            freeMode: true,
            watchSlidesProgress: true,
            centerInsufficientSlides: true,
            breakpoints: {
                // using bootstrap breakpoints and just eyeballing what looks nice
                576: {
                    slidesPerView: 5, // sm
                    loop: numThumbs >= 5,
                },
                768: {
                    slidesPerView: 6, // md
                    loop: numThumbs >= 6,
                },
                992: {
                    slidesPerView: 7, // lg
                    loop: numThumbs >= 7,
                },
                1200: {
                    slidesPerView: 6, // xl
                    loop: numThumbs >= 6,
                },
                1400: {
                    slidesPerView: 7, // xxl
                    loop: numThumbs >= 7,
                },
            },
        });
    }
    const numSlides = options.element.querySelectorAll('.swiper-slide').length;
    const main = new Swiper(options.element, {
        loop: numSlides > 1,
        spaceBetween: 0,
        autoplay: {
            delay: 4000,
            pauseOnMouseEnter: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
            addIcons: false,
        },
        thumbs: {
            swiper: thumbs,
        },
    });

    return {
        main,
        thumbs,
        destroy: function () {
            main.destroy();
            thumbs?.destroy();
        },
    };
};
