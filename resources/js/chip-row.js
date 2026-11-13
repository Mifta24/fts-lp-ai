/**
 * Marks whether the quick-chip row can scroll and whether it sits at its
 * start or end, so the stylesheet can fade only the edges where more chips
 * are waiting.
 */
function initChipRow() {
    document.querySelectorAll('.chat-chips').forEach((row) => {
        function update() {
            const max = row.scrollWidth - row.clientWidth;
            row.classList.toggle('is-scrollable', max > 2);
            row.classList.toggle('is-at-start', row.scrollLeft <= 2);
            row.classList.toggle('is-at-end', row.scrollLeft >= max - 2);
        }

        row.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        document.fonts?.ready.then(update);
        update();
    });
}

document.addEventListener('DOMContentLoaded', initChipRow);
