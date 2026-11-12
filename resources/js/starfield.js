/**
 * Draws twinkling stars on a canvas that fills its parent.
 * With prefers-reduced-motion the stars are drawn once and never animate.
 * Returns a function that stops the animation and removes the listeners.
 */
export function startStarfield(canvas) {
    const ctx = canvas.getContext('2d');
    if (!ctx) return () => {};

    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    let stars = [];
    let width = 0;
    let height = 0;
    let frame = 0;

    function draw(time) {
        ctx.clearRect(0, 0, width, height);
        ctx.fillStyle = '#fff';
        for (const star of stars) {
            ctx.globalAlpha = reduced ? star.alpha : star.alpha * (0.6 + 0.4 * Math.sin(time * star.speed + star.phase));
            ctx.beginPath();
            ctx.arc(star.x, star.y, star.radius, 0, Math.PI * 2);
            ctx.fill();
        }
        ctx.globalAlpha = 1;
        if (!reduced) frame = requestAnimationFrame(draw);
    }

    function resize() {
        cancelAnimationFrame(frame);
        const rect = canvas.getBoundingClientRect();
        width = rect.width;
        height = rect.height;
        canvas.width = Math.round(width * dpr);
        canvas.height = Math.round(height * dpr);
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        const count = Math.min(400, Math.round((width * height) / 6000));
        stars = Array.from({ length: count }, () => ({
            x: Math.random() * width,
            y: Math.random() * height,
            radius: Math.random() * 1.2 + 0.3,
            alpha: Math.random() * 0.6 + 0.3,
            speed: Math.random() * 0.002 + 0.0005,
            phase: Math.random() * Math.PI * 2,
        }));
        draw(0);
    }

    resize();
    window.addEventListener('resize', resize);

    return () => {
        cancelAnimationFrame(frame);
        window.removeEventListener('resize', resize);
    };
}
