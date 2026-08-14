import throttle from 'lodash.throttle';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.vault-items').forEach((con) => {
        const hs = con.querySelector('.horizontal-scroll') as HTMLElement;
        if (!hs) return;

        function onScroll() {
            const scroll = hs.scrollLeft;
            const max = hs.scrollWidth - hs.offsetWidth;
            const pct = (scroll / max) * 100;

            if (pct > 5) con.classList.add('scroll-left');
            else con.classList.remove('scroll-left');

            if (pct < 95) con.classList.add('scroll-right');
            else con.classList.remove('scroll-right');
        }

        hs.addEventListener('scroll', throttle(onScroll, 100));
        requestAnimationFrame(onScroll);
    });
});
