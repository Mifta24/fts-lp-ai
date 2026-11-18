/**
 * Full-screen stage chrome shared by the opening screen and the lobby:
 * hides the "preparing" portal once the hero image is ready (it only ever
 * shows when a scene or navigation is genuinely slow), and eases the
 * scene out before following an "enter" link so moving between screens feels
 * like walking through the lobby rather than loading a page.
 *
 * A photo can travel with the guest: when the link or card they tap has a room
 * photo, it is named for the browser's view transition so that, on the room
 * page, it grows into the big photo instead of the page simply changing.
 *
 * A link may carry `data-topic`: the question the guest picked from the menu.
 * It is handed to the AI concierge, which raises it once the next scene is up
 * (or straight away when the guest is already in that scene).
 */
const TOPIC_KEY = 'concierge_pending_topic';

/** Set while the portal loader is on screen as the guest leaves, so the next scene opens on it instead of flashing. */
const PORTAL_KEY = 'fts_portal';

/** How long a navigation may take before the portal loader is shown at all; quicker ones never see it. */
const PORTAL_DELAY_MS = 420;

function initStage() {
    const stage = document.querySelector('.stage');
    if (!stage) return;

    const loader = stage.querySelector('[data-stage-loader]');
    const image = stage.querySelector('.stage-image');
    const tour = stage.querySelector('[data-stage-tour]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let portalTimer = null;

    /**
     * The scene is ready: a loader that never got to show is dropped at once,
     * one that is on screen fades out from wherever it is.
     */
    function showScene() {
        if (!loader) return;

        const opacity = parseFloat(getComputedStyle(loader).opacity) || 0;
        document.documentElement.removeAttribute('data-portal');
        loader.classList.remove('is-active');

        if (opacity < 0.02) {
            loader.classList.add('is-ready');
            loader.hidden = true;

            return;
        }

        loader.style.setProperty('--portal-from', String(opacity));
        loader.classList.add('is-done');
        loader.addEventListener('animationend', () => { loader.hidden = true; }, { once: true });
    }

    /** The next scene is slow to arrive: open the portal over the one being left. */
    function showPortal() {
        if (!loader) return;

        loader.hidden = false;
        loader.classList.remove('is-ready', 'is-done');
        loader.classList.add('is-active');

        try { sessionStorage.setItem(PORTAL_KEY, '1'); } catch { /* the next scene just opens without it */ }
    }

    if (!image || image.complete) {
        showScene();
    } else {
        image.addEventListener('load', showScene, { once: true });
        image.addEventListener('error', showScene, { once: true });
    }

    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            leaving = false;
            window.clearTimeout(portalTimer);
            try { sessionStorage.removeItem(PORTAL_KEY); } catch { /* nothing to clear */ }
            if (loader) {
                loader.classList.remove('is-active', 'is-done');
                loader.classList.add('is-ready');
                loader.hidden = true;
            }
            stage.classList.remove('is-leaving', 'is-morphing');
            document.querySelectorAll('[data-morph]').forEach((photo) => {
                photo.style.viewTransitionName = '';
                photo.removeAttribute('data-morph');
            });
            tour?.classList.remove('is-visible');
        }
    });

    let leaving = false;

    /** Ease the stage out, then walk the guest over to `href`. */
    function leave(href, line = null, topic = null, { photo = null } = {}) {
        if (leaving) return;
        leaving = true;

        if (photo) {
            photo.style.viewTransitionName = 'room-photo';
            photo.setAttribute('data-morph', '');
            stage.classList.add('is-morphing');
        }

        try {
            if (topic) sessionStorage.setItem(TOPIC_KEY, topic);
            else sessionStorage.removeItem(TOPIC_KEY);
        } catch { /* private mode: the scene still changes, only the topic is dropped */ }

        // The concierge says where she is taking the guest, then the scene
        // walks forward into the next one.
        if (line && tour) {
            tour.textContent = line;
            tour.classList.add('is-visible');
        }

        stage.classList.add('is-leaving');

        const chime = window.hotelSound?.isEnabled() ?? false;
        if (chime) window.hotelSound.play('enter');

        const hold = reducedMotion ? 0 : line ? 820 : chime ? 560 : 320;
        window.setTimeout(() => {
            window.location.href = href;
            portalTimer = window.setTimeout(showPortal, PORTAL_DELAY_MS);
        }, hold);
    }

    document.querySelectorAll('[data-stage-exit]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const modified = event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0;
            if (event.defaultPrevented || modified || link.target === '_blank') return;

            event.preventDefault();

            // Already in this scene: no walk needed, the concierge just takes
            // the topic up where the guest is standing.
            const samePlace = link.pathname === window.location.pathname;
            if (samePlace && link.dataset.topic) {
                window.dispatchEvent(new CustomEvent('concierge:ask', { detail: { message: link.dataset.topic } }));
                return;
            }
            if (samePlace) return;

            const photo = /\/rooms\/[^/]+$/.test(link.pathname) ? link.querySelector('img') : null;
            leave(link.href, link.dataset.tourLine, link.dataset.topic, { photo });
        });
    });

    window.hotelStage = { leave };
}

/**
 * The big room photo only carries a transition name while the page is being
 * revealed. Left on permanently, the browser paints it outside the scrolling
 * panel instead of clipping it to the panel's edge.
 */
window.addEventListener('pagereveal', (event) => {
    const photo = document.querySelector('[data-room-photo]');
    if (!photo || !event.viewTransition) return;

    photo.style.viewTransitionName = 'room-photo';
    event.viewTransition.finished.finally(() => { photo.style.viewTransitionName = ''; });
});

/** The topic the guest picked on the previous scene, if any (read once). */
window.takePendingConciergeTopic = () => {
    try {
        const topic = sessionStorage.getItem(TOPIC_KEY);
        sessionStorage.removeItem(TOPIC_KEY);
        return topic;
    } catch {
        return null;
    }
};

document.addEventListener('DOMContentLoaded', initStage);
