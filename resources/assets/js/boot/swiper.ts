import { Swiper } from 'swiper/bundle';

type InitSwiperOptions = {
    element: HTMLElement;
    thumbnailsElement: HTMLElement | null;
};

(window as any).initialiseSwiper = function (options: InitSwiperOptions) {
    let thumbs: Swiper | null = null;
    if (options.thumbnailsElement) {
        thumbs = new Swiper(options.thumbnailsElement, {
            loop: true,
            spaceBetween: 5,
            slidesPerView: 4, // xs
            freeMode: true,
            watchSlidesProgress: true,
            centerInsufficientSlides: true,
            breakpoints: {
                // using bootstrap breakpoints and just eyeballing what looks nice
                576: {
                    slidesPerView: 5, // sm
                },
                768: {
                    slidesPerView: 6, // md
                },
                992: {
                    slidesPerView: 7, // lg
                },
                1200: {
                    slidesPerView: 6, // xl
                },
                1400: {
                    slidesPerView: 7, // xxl
                },
            },
        });
    }
    const main = new Swiper(options.element, {
        loop: true,
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
