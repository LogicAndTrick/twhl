import { filteredEventListener } from '../lib/utils';

document.addEventListener('DOMContentLoaded', () => {
    filteredEventListener(document, 'click', '.stop-close', (e) => {
        e.stopPropagation();
    });

    filteredEventListener(document, 'click', '.nice-date', (e, el) => {
        e.preventDefault();
        e.stopPropagation();
        el.classList.toggle('on');
    });

    filteredEventListener(document, 'click', '.spoiler', (e, el) => {
        e.preventDefault();
        e.stopPropagation();
        el.classList.toggle('on');
    });
});
