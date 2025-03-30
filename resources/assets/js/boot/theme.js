/**
 * Get the saved theme if we have one, otherwise use the browser preference.
 */
function getTheme() {
    const stored = localStorage.getItem('theme');
    if (stored === 'light' || stored === 'dark') return stored;

    const media = window.matchMedia('(prefers-color-scheme: dark)');
    return media.matches ? 'dark' : 'light';
}

// set the theme immediately
document.documentElement.setAttribute('data-bs-theme', getTheme());

/**
 * Set the theme and save it to local storage.
 */
function setTheme(theme) {
    document.documentElement.setAttribute('data-bs-theme', theme);
    localStorage.setItem('theme', theme);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.theme-toggle').forEach(toggle => {
        toggle.addEventListener('click', e => {
            e.preventDefault();
            setTheme(document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark');
        })
    });
});