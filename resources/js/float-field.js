/**
 * The floating field in the list rooms (services, AI solutions, portfolio):
 * cards fly in as they come into view, the field drifts against the pointer
 * (each card by its own --depth), and the card under the cursor tilts toward
 * it with a light that follows. Tapping a card sends it forward before the
 * stage walks to its page. Everything is skipped for guests who ask for less
 * motion; the cards then simply sit still.
 */
const EASE = 0.07;
const SETTLED = 0.002;
const MAX_TILT = 7;
const REVEAL_STEP = 70;

function initFloatField() {
    const field = document.querySelector('[data-float-field]');
    if (!field || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const cards = [...field.querySelectorAll('.facility-card')];
    if (!cards.length) return;

    revealOnView(field, cards);
    driftWithPointer(field);
    cards.forEach(tiltTowardPointer);
    launchOnTap(cards);
}

/** Cards arrive in small waves as the guest scrolls them into view. */
function revealOnView(field, cards) {
    if (!('IntersectionObserver' in window)) return;

    field.classList.add('is-armed');

    let wave = [];
    let waveTimer = null;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            wave.push(entry.target);
            observer.unobserve(entry.target);
        });

        if (wave.length && !waveTimer) {
            waveTimer = requestAnimationFrame(() => {
                wave.forEach((card, order) => {
                    card.style.setProperty('--reveal-delay', `${120 + order * REVEAL_STEP}ms`);
                    card.classList.add('is-seen');
                });
                wave = [];
                waveTimer = null;
            });
        }
    }, { root: field, rootMargin: '0px 0px -8% 0px', threshold: 0.05 });

    cards.forEach((card) => observer.observe(card));
}

/** The whole field shifts a little against the pointer, eased so it glides. */
function driftWithPointer(field) {
    let targetX = 0;
    let targetY = 0;
    let x = 0;
    let y = 0;
    let frame = null;

    function tick() {
        x += (targetX - x) * EASE;
        y += (targetY - y) * EASE;
        field.style.setProperty('--px', x.toFixed(3));
        field.style.setProperty('--py', y.toFixed(3));
        frame = Math.abs(targetX - x) > SETTLED || Math.abs(targetY - y) > SETTLED ? requestAnimationFrame(tick) : null;
    }

    window.addEventListener('pointermove', (event) => {
        if (event.pointerType !== 'mouse') return;

        targetX = (event.clientX / window.innerWidth - 0.5) * 2;
        targetY = (event.clientY / window.innerHeight - 0.5) * 2;
        if (!frame) frame = requestAnimationFrame(tick);
    }, { passive: true });
}

/** The hovered card leans toward the cursor and a light follows it. */
function tiltTowardPointer(card) {
    card.addEventListener('pointermove', (event) => {
        if (event.pointerType !== 'mouse') return;

        const box = card.getBoundingClientRect();
        const across = (event.clientX - box.left) / box.width;
        const down = (event.clientY - box.top) / box.height;

        card.style.setProperty('--tilt-y', `${((across - 0.5) * 2 * MAX_TILT).toFixed(2)}deg`);
        card.style.setProperty('--tilt-x', `${((0.5 - down) * 2 * MAX_TILT).toFixed(2)}deg`);
        card.style.setProperty('--mx', `${(across * 100).toFixed(1)}%`);
        card.style.setProperty('--my', `${(down * 100).toFixed(1)}%`);
    });

    card.addEventListener('pointerleave', () => {
        card.style.setProperty('--tilt-x', '0deg');
        card.style.setProperty('--tilt-y', '0deg');
    });
}

/** A tapped card comes forward; stage.js then walks to its page. */
function launchOnTap(cards) {
    const stage = document.querySelector('.stage');

    cards.forEach((card) => {
        card.addEventListener('click', (event) => {
            const modified = event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0;
            if (modified) return;

            card.classList.add('is-launching');
            stage?.classList.add('is-card-launch');
        });
    });

    // Coming back with the browser's back button restores the page as it was left.
    window.addEventListener('pageshow', (event) => {
        if (!event.persisted) return;

        cards.forEach((card) => card.classList.remove('is-launching'));
        stage?.classList.remove('is-card-launch', 'is-leaving');
    });
}

document.addEventListener('DOMContentLoaded', initFloatField);
