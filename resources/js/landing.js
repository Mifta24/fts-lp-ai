import { startStarfield } from './starfield';
import { buildMailto, formatPrice, validateForm, DEFAULT_USD_RATE } from './lib';

function initNav() {
    const toggle = document.querySelector('[data-nav-toggle]');
    const menu = document.getElementById('mobile-menu');
    if (!toggle || !menu) return;

    const setOpen = (open) => {
        menu.classList.toggle('hidden', !open);
        toggle.setAttribute('aria-expanded', String(open));
    };

    toggle.addEventListener('click', () => setOpen(menu.classList.contains('hidden')));
    menu.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setOpen(false)));
}

/** Marks the desktop nav link of the section that is currently in view. */
function initScrollSpy() {
    const links = [...document.querySelectorAll('[data-nav-link]')];
    const sections = links.map((link) => document.querySelector(link.getAttribute('href'))).filter(Boolean);
    if (!sections.length || !('IntersectionObserver' in window)) return;

    const observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (!entry.isIntersecting) continue;
                links.forEach((link) => {
                    link.dataset.active = String(link.getAttribute('href') === `#${entry.target.id}`);
                });
            }
        },
        { rootMargin: '-45% 0px -50% 0px' },
    );

    sections.forEach((section) => observer.observe(section));
}

function initPricing() {
    const section = document.getElementById('pricing');
    if (!section) return;

    const rate = Number(section.dataset.usdRate) || DEFAULT_USD_RATE;
    const buttons = section.querySelectorAll('[data-currency]');

    const render = (currency) => {
        section.querySelectorAll('.price[data-idr]').forEach((el) => {
            el.textContent = formatPrice(Number(el.dataset.idr), currency, rate);
        });
        buttons.forEach((button) => button.setAttribute('aria-pressed', String(button.dataset.currency === currency)));
    };

    buttons.forEach((button) => button.addEventListener('click', () => render(button.dataset.currency)));
}

function initContactForm() {
    const form = document.getElementById('contact-form');
    const error = document.getElementById('form-error');
    if (!form || !error) return;

    form.addEventListener('submit', (event) => {
        event.preventDefault();

        const fields = {};
        for (const name of ['name', 'email', 'company', 'subject', 'message']) {
            fields[name] = form.elements[name].value;
        }

        const missing = validateForm(fields);
        for (const element of form.elements) {
            if (element.name) element.removeAttribute('aria-invalid');
        }

        if (missing.length) {
            error.hidden = false;
            missing.forEach((name) => form.elements[name].setAttribute('aria-invalid', 'true'));
            form.elements[missing[0]].focus();
            return;
        }

        error.hidden = true;
        window.location.href = buildMailto(fields, form.dataset.mailTo);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('starfield');
    if (canvas) startStarfield(canvas);

    initNav();
    initScrollSpy();
    initPricing();
    initContactForm();
});
