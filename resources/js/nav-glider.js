/**
 * The header navigation's sliding highlight: one glowing pill that rests on
 * the current room and glides to whichever link the pointer or keyboard
 * focus is on, then springs back when it leaves. Without JavaScript the
 * current link keeps its own background instead.
 */
function initNavGlider() {
    const nav = document.querySelector('.stage-nav');
    const glider = nav?.querySelector('[data-nav-glider]');
    if (!nav || !glider) return;

    const links = [...nav.querySelectorAll('a')];
    const current = links.find((link) => link.getAttribute('aria-current') === 'page');
    let lit = null;

    function moveTo(link, { instant = false } = {}) {
        if (lit) lit.classList.remove('is-lit');
        lit = link;

        if (!link) {
            glider.classList.remove('is-visible');
            return;
        }

        link.classList.add('is-lit');
        if (instant) glider.classList.add('is-instant');
        glider.style.setProperty('--glide-x', `${link.offsetLeft}px`);
        glider.style.setProperty('--glide-w', `${link.offsetWidth}px`);
        glider.classList.add('is-visible');
        if (instant) requestAnimationFrame(() => requestAnimationFrame(() => glider.classList.remove('is-instant')));
    }

    nav.classList.add('has-glider');
    moveTo(current, { instant: true });

    links.forEach((link) => {
        link.addEventListener('pointerenter', () => moveTo(link));
        link.addEventListener('focus', () => moveTo(link));
    });
    nav.addEventListener('pointerleave', () => moveTo(current));
    nav.addEventListener('focusout', (event) => {
        if (!nav.contains(event.relatedTarget)) moveTo(current);
    });

    // Fonts arriving late change the link widths, so measure again then.
    document.fonts?.ready.then(() => moveTo(lit ?? current, { instant: true }));
    window.addEventListener('resize', () => moveTo(lit ?? current, { instant: true }));
}

document.addEventListener('DOMContentLoaded', initNavGlider);
