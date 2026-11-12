# FTS AI Landing Page Implementation Plan (Laravel version)

> Spec: `docs/superpowers/specs/2026-11-12-fts-ai-landing-design.md`. One commit per task, pushed to `origin/main`.

**Goal:** A Laravel + Tailwind landing page for FTS, themed like fts-tech.co.id, with an avatar hero and ID/EN/JA text.

## Global Constraints

- Languages `id` (default), `en`, `ja`. `?lang=` plus cookie `fts_lang`. Unknown value falls back to `id`.
- Contact values come from `.env` (`FTS_WHATSAPP`, `FTS_EMAIL`, `FTS_PHONE`, `FTS_USD_RATE`) through `config/fts.php`.
- Prices: Rp 700.000, 1.200.000, 1.600.000 per month for 5, 10, 15 pages. 36-month contract, last 6 months free. Design and development free.
- No database, no external JS libraries. Works at 360px without horizontal scroll.

## Review Focus

- `?lang=xx` (unknown) renders Indonesian, not an error.
- A language cookie with an unknown value is ignored.
- Form with `&`, `#`, line breaks keeps its text in the `mailto:` link.
- Form with an empty required field does not open the mail app.
- Missing avatar image: hero stays readable.

## Tasks

### Task 1: Strip the hotel app to a Laravel skeleton
Remove all hotel code, tests, docs, DB, and unused packages. One route `/` renders `landing.blade.php`. Test: `LandingPageTest` (home shows "Fujiyama Technology Solutions" and no "Hotel"; `/admin` and `/demo/rooms` are 404). Verify: `php artisan test` green, `composer audit` clean.

### Task 2: Theme, header, hero
Tailwind theme tokens from the spec, Inter via Vite fonts, layout Blade component, header with language switch and WhatsApp link, hero with starfield canvas, badge, headline, tiles, buttons, avatar. Test: hero text for each language. Verify: `npm run build` succeeds, page renders at 1440 and 375 px.

### Task 3: Content sections
About, services, how it works, pricing, team and values, contact, footer, with `lang/{id,en,ja}/landing.php`. Test: a key-parity test (all languages have the same keys, none empty) and a test that every language renders its pricing heading. Verify: no horizontal scroll at 360px.

### Task 4: Behavior
`SetLocale` middleware (query, then cookie, then `id`), `config/fts.php`, `resources/js/landing.js` with `formatPrice`, `buildMailto`, `validateForm`, starfield, mobile nav, USD toggle, form. Tests: PHPUnit for locale resolution (query, cookie, unknown values); Node tests for the JS functions (`formatPrice(700000,'USD') === '$42.42'`, `buildMailto` encodes `a&b #1\nline2` as `a%26b%20%231%0Aline2`, `validateForm` returns empty required fields). Verify in the browser: language switch, toggle, form error.

### Task 5: Docs, CI, final checks
README (setup, avatar and logo replacement, deploy), GitHub Actions CI (PHP tests, Node tests, Vite build), final browser checks at 1440, 768, 375, 360 px, keyboard focus, avatar-missing fallback. Verify: all tests green locally, CI file valid.
