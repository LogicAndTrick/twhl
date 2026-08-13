/**
 * Get the saved theme if we have one, otherwise use the browser preference.
 */
export function getTheme() {
    const stored = localStorage.getItem('theme');
    if (stored === 'light' || stored === 'dark') return stored;

    const media = window.matchMedia('(prefers-color-scheme: dark)');
    return media.matches ? 'dark' : 'light';
}

export function detectAndSetTheme() {
    document.documentElement.setAttribute('data-bs-theme', getTheme());
}

// set the theme immediately
detectAndSetTheme();

/**
 * Set the theme and save it to local storage.
 */
export function setTheme(theme: string) {
    document.documentElement.setAttribute('data-bs-theme', theme);
    localStorage.setItem('theme', theme);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.theme-toggle').forEach((toggle) => {
        toggle.addEventListener('click', (e) => {
            e.preventDefault();
            setTheme(document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark');
        });
    });
});
