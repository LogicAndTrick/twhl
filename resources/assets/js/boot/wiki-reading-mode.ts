import Cookies from 'js-cookie';

document.addEventListener('DOMContentLoaded', function () {
    const readingModeCheckbox = document.getElementById('reading-mode') as HTMLInputElement;
    if (!(readingModeCheckbox instanceof HTMLInputElement)) return;

    const content = document.querySelector('.wiki.bbcode') as HTMLElement;
    if (!content) return;

    function setReadingMode(on: boolean) {
        content!.classList.toggle('reading-mode', on);
        readingModeCheckbox!.checked = on;
    }

    if (!readingModeCheckbox || !content) return;

    readingModeCheckbox.addEventListener('change', () => {
        const on = readingModeCheckbox.checked;
        setReadingMode(on);
        Cookies.set('wiki.reading-mode', on ? 'true' : 'false');
    });

    setReadingMode(Cookies.get('wiki.reading-mode') === 'true');
});
