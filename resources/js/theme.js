/**
 * Light is the default theme; the header switch turns the dark one on and
 * off. The choice is kept per browser (the <head> applies it before the
 * page paints) and colours cross-fade briefly when it changes.
 */
const STORAGE_KEY = 'fts_theme';

function isDark() {
    return document.documentElement.dataset.theme === 'dark';
}

function syncToggles() {
    const dark = isDark();

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-pressed', String(dark));
        button.setAttribute('aria-label', dark ? button.dataset.labelLight : button.dataset.labelDark);
        button.title = button.getAttribute('aria-label');
    });
}

function setTheme(dark) {
    const root = document.documentElement;

    root.classList.add('is-theme-switching');
    if (dark) root.dataset.theme = 'dark';
    else delete root.dataset.theme;

    try {
        localStorage.setItem(STORAGE_KEY, dark ? 'dark' : 'light');
    } catch { /* the choice just won't be remembered */ }

    syncToggles();
    window.setTimeout(() => root.classList.remove('is-theme-switching'), 450);
}

function initThemeToggle() {
    syncToggles();

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => setTheme(! isDark()));
    });
}

document.addEventListener('DOMContentLoaded', initThemeToggle);
