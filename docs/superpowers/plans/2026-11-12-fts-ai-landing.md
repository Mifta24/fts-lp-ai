# FTS AI Landing Page Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a static, three-language (ID/EN/JA) AI landing page for FTS with an immersive avatar hero and scrollable company sections.

**Architecture:** Plain HTML/CSS/JS, no build step. All visible text lives in `i18n.js` and is bound to the markup with `data-i18n` attributes. `app.js` holds behavior, and its pure functions are also exported for Node tests.

**Tech Stack:** HTML5, CSS3 (custom properties), vanilla JS, Node (only for `node --test`).

**Spec:** `docs/superpowers/specs/2026-11-12-fts-ai-landing-design.md`

## Global Constraints

- Languages: `id` (default), `en`, `ja`. Language choice stored in `localStorage` key `fts-lang`.
- Theme: dark background with green accent (FTS brand). No gold or navy.
- No framework, no build step, no backend, no external JS libraries.
- Works from 360px width with no horizontal scroll. Text contrast meets WCAG AA. Visible keyboard focus.
- Prices: Rp 700.000, Rp 1.200.000, Rp 1.600.000 per month for 5, 10, 15 pages. Contract 36 months, last 6 months free. Design and development free. More than 15 pages is by quotation.
- USD rate: 1 USD = Rp 16.500 (matches the current site: 700.000 → $42.42).
- Contact: info@fts-tech.co.id, +62 895 2933 6179, Neo Soho Mall, Jalan Let. Jend. S. Parman Kav. 28 Unit 2011, Tanjung Duren Selatan, Grogol Petamburan, West Jakarta 11470. Hours Mon–Fri 09:00–18:00.
- WhatsApp button uses `+62 895 2933 6179` (`https://wa.me/6289529336179`) until the user picks another number.
- No git repo exists here, so there are no commit steps. Each task ends with a verification step instead.

## Review Focus

- `localStorage` throws (private window or blocked): page loads in Indonesian and the switch still works for the session.
- Stored language is an unknown value such as `"xx"`: fall back to `id`.
- Form text containing `&`, `#`, `?`, or line breaks: the `mailto:` link must keep it intact.
- Form submitted with an empty required field: no mail link opens and the field is flagged.
- Avatar image file missing: hero still shows a readable gradient background.
- Page at 360px width: no horizontal scroll.

## File Structure

```
index.html   markup, data-i18n keys
styles.css   tokens, layout, hero, glass cards, responsive
i18n.js      window.I18N = { id, en, ja }; module.exports for Node
app.js       behavior; pure functions exported for Node
assets/      avatar.svg (placeholder), logo.svg (wordmark placeholder)
tests/       i18n.test.js, app.test.js
```

---

### Task 1: Translation dictionary

**Files:**
- Create: `i18n.js`
- Test: `tests/i18n.test.js`

**Interfaces:**
- Produces: `I18N: { id: Dict, en: Dict, ja: Dict }` where `Dict = Record<string, string>`. Exposed as `window.I18N` in the browser and `module.exports` in Node. Key naming: `section.item` in lowercase dots, for example `hero.title`, `svc.web.title`, `price.p5.name`, `contact.form.send`.

- [ ] **Step 1: Write `tests/i18n.test.js`** using `node:test`. Tests: `all languages have identical key sets`; `no value is an empty string`; `every data-i18n and data-i18n-placeholder key used in index.html exists in id` (skip this test while `index.html` does not exist yet, and it becomes active in Task 2).
- [ ] **Step 2: Run `node --test tests/` and confirm the first two tests fail** (`i18n.js` missing).
- [ ] **Step 3: Create `i18n.js`** with keys covering: nav and header, hero (label, title, intro, four pill buttons), four service cards (title, description, three bullets each, plus the restaurant case-study line), three how-it-works steps, pricing (heading, plan names, `/month` text, contract note, quote text, toggle labels), team (heading, four names, roles, shortened bios), four values, contact (heading, address, hours, form labels, send button, error text, WhatsApp button), footer. Indonesian and English copy comes from the current site text; Japanese is translated.
- [ ] **Step 4: Run `node --test tests/`** and confirm both i18n tests pass.

### Task 2: Theme, header, and hero

**Files:**
- Create: `index.html`, `styles.css`, `assets/avatar.svg`, `assets/logo.svg`

**Interfaces:**
- Consumes: i18n keys from Task 1 (`hero.*`, `nav.*`).
- Produces: CSS custom properties on `:root` (`--bg`, `--surface`, `--text`, `--muted`, `--accent`, `--accent-ink`, `--radius`), a `.glass` card class, a `.pill` button class, and section ids `#services`, `#how`, `#pricing`, `#team`, `#contact`. Header contains `<div class="lang-switch">` with three `<button data-lang="id|en|ja">`. Hero pills are `<a class="pill" href="#svc-web|#svc-ai|#svc-agent|#svc-offshore">`.

- [ ] **Step 1: Create `assets/avatar.svg`** (placeholder illustration, dark green gradient with a simple abstract figure) and `assets/logo.svg` (text wordmark "FTS").
- [ ] **Step 2: Create `index.html`** with `<html lang="id">`, meta description, Open Graph tags, a fixed header (logo, language switch, WhatsApp link), and the hero: 100vh with avatar background, overlay, glass card at bottom-left (label, `<h1>`, intro) and the four pills.
- [ ] **Step 3: Create `styles.css`** with the tokens above, hero layout, glass card, pill styling, visible `:focus-visible` outlines, and a hero gradient fallback when the image fails.
- [ ] **Step 4: Run `node --test tests/`** and confirm the HTML-keys test is now active and passes.
- [ ] **Step 5: Open `index.html` in the browser** at 1440px and 375px. Expected: hero fills the screen, card and four pills are readable, no horizontal scroll.

### Task 3: Content sections

**Files:**
- Modify: `index.html` (append sections and footer), `styles.css` (section styles)

**Interfaces:**
- Consumes: Task 2 tokens, `.glass`, `.pill`, and section ids.
- Produces: service cards with ids `svc-web`, `svc-ai`, `svc-agent`, `svc-offshore`; pricing amounts as `<span class="price" data-idr="700000|1200000|1600000">`; currency toggle as `<button data-currency="IDR|USD">`; contact form `<form id="contact-form">` with fields `name`, `email`, `company`, `subject`, `message` (`name`, `email`, `message` required) and an error element `<p id="form-error" hidden>`.

- [ ] **Step 1: Add the services section** (four glass cards with three bullets each, restaurant case-study line under Web Development) and the how-it-works section (three steps).
- [ ] **Step 2: Add pricing** (three plan cards plus a quote card, toggle, contract note) and team and values (four members, four values in one row).
- [ ] **Step 3: Add contact** (address, hours, phone, email, form, WhatsApp button) and footer.
- [ ] **Step 4: Style all sections** with a responsive grid: 1 column under 640px, 2 columns up to 1024px, 4 columns for services above that.
- [ ] **Step 5: Run `node --test tests/`** (every new `data-i18n` key must exist in `i18n.js`) and check 360px width in the browser. Expected: tests pass, no horizontal scroll.

### Task 4: Behavior

**Files:**
- Create: `app.js`
- Test: `tests/app.test.js`
- Modify: `index.html` (load `i18n.js` then `app.js` with `defer`)

**Interfaces:**
- Consumes: `window.I18N` (Task 1), the markup hooks from Tasks 2 and 3.
- Produces (all exported with `module.exports` when `module` exists):
  - `resolveLang(stored: string | null | undefined): 'id' | 'en' | 'ja'` returns `'id'` for anything unknown.
  - `formatPrice(idr: number, currency: 'IDR' | 'USD'): string` returns `"Rp 700.000"` for IDR and `"$42.42"` for USD (rate 16.500).
  - `buildMailto(fields: {name, email, company, subject, message}): string` returns a `mailto:info@fts-tech.co.id?subject=…&body=…` URL with `encodeURIComponent` on every value.
  - `validateForm(fields): string[]` returns the names of empty required fields.
  - `applyLang(lang)` sets `<html lang>`, `document.title`, all `data-i18n` text, `data-i18n-placeholder` attributes, the active state of the switch buttons, and saves to `localStorage` inside try/catch.

- [ ] **Step 1: Write `tests/app.test.js`** with these tests and assertions:
  - `resolveLang("xx") === "id"`, `resolveLang(null) === "id"`, `resolveLang("ja") === "ja"`.
  - `formatPrice(700000, "IDR") === "Rp 700.000"`, `formatPrice(1200000, "USD") === "$72.73"`, `formatPrice(1600000, "USD") === "$96.97"`.
  - `buildMailto` with message `"a&b #1\nline2"` returns a string containing `a%26b%20%231%0Aline2` and starting with `mailto:info@fts-tech.co.id`.
  - `validateForm({name:"", email:"a@b.c", message:""})` equals `["name", "message"]`.
- [ ] **Step 2: Run `node --test tests/app.test.js`** and confirm it fails (`app.js` missing).
- [ ] **Step 3: Implement the four pure functions in `app.js`**, then the browser wiring behind a `typeof document !== 'undefined'` guard: read language on load (try/catch around `localStorage`), bind switch buttons, bind currency toggle by rewriting every `.price` from its `data-idr`, bind the form submit (prevent default, `validateForm`, show `#form-error` or set `window.location.href` to the `buildMailto` result), and smooth scroll that is disabled when `prefers-reduced-motion` is set.
- [ ] **Step 4: Run `node --test tests/`** and confirm all tests pass.
- [ ] **Step 5: In the browser**, click ID/EN/JA and check text, `<html lang>`, and title change and survive reload. Toggle IDR/USD. Submit an empty form (expect the error) and a filled one (expect a mail client prompt). Check the console for errors.

### Task 5: Final verification and handoff

**Files:**
- Modify: `index.html` or `styles.css` only if a defect is found.

- [ ] **Step 1: Break the avatar path temporarily** (rename `assets/avatar.svg`) and confirm the hero still shows a readable gradient. Restore the file.
- [ ] **Step 2: Test at 1440px, 768px, 375px, and 360px.** Expected: every section renders, hero pills scroll to the matching service cards, no horizontal scroll, no console errors.
- [ ] **Step 3: Test keyboard navigation** (Tab through header, pills, toggles, and form). Expected: visible focus on every control.
- [ ] **Step 4: Write a short `README.md`** covering how to replace the avatar image, logo, and WhatsApp number, and how to upload the folder to Cloudflare Pages.
- [ ] **Step 5: Report** what was verified and what the user still must supply (real avatar, logo, EN/JA text review, WhatsApp number choice).
