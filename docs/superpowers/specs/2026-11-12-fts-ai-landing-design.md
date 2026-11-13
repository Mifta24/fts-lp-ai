# FTS AI Landing Page — Design Spec

Date: 2026-11-12
Replaces: https://fts-tech.co.id/ (company profile)
Reference style: https://hotel-ai.fts-tech.co.id/

## Goal

Turn the Fujiyama Technology Solutions (FTS) company profile into an AI-focused landing page. It uses the immersive hero style of the Hotel AI Concierge site, and it still works as a company profile. Success means visitors understand FTS sells AI solutions and reach an inquiry (WhatsApp, form, or email).

## Decisions from the brief

- Format: **hybrid**. An immersive full-screen hero with an AI avatar, followed by scrollable sections.
- Avatar: **visual only plus a greeting**. No chat, no backend, no AI API.
- Languages: Indonesian (default), English, Japanese.
- Brand: keep FTS's dark theme with green accent. Do not copy the hotel site's gold and navy.

## Approach

A static single page in plain HTML, CSS, and JS. No framework and no build step. The output is a folder that can be uploaded to any static host (for example Cloudflare Pages). The source of the current fts-tech.co.id is not available, so this is a replacement build, not an edit.

## Files

```
index.html      markup, all sections, data-i18n keys
styles.css      theme tokens, layout, hero, glass cards, responsive rules
i18n.js         ID/EN/JA dictionary, one object per language
app.js          language switch, currency toggle, smooth scroll, form handling
assets/         avatar image (placeholder), logo, og image
```

Each file has one job. `i18n.js` holds only text. `app.js` holds only behavior.

## Page structure

1. **Header** (fixed, translucent): FTS logo, language switch ID/EN/JA, WhatsApp button.
2. **Hero** (100vh): avatar image as background with a dark overlay. A glass card at bottom-left holds the label "AI SOLUTIONS", a greeting headline, and one sentence about FTS. A row of pill buttons follows: Web Development, AI Automation, AI Agent, Offshore Dev. Each button scrolls to its service card.
3. **AI Services**: four glass cards (Web Development, AI Automation, AI Agent Development, Offshore Development), taken from the current site's copy and bullets. Restaurant websites appear as a case-study line under Web Development.
4. **How it works**: three steps (Consult, Build, Run).
5. **Pricing**: plans for 5, 10, and 15 pages, plus a quote option for more than 15. IDR/USD toggle. Figures come from the current site: Rp 700.000, 1.200.000, and 1.600.000 per month, 36-month contract with the last 6 months free, and free design and development.
6. **Team and values**: four team members with shortened bios (Yoshihiro Nakagawa, Takakazu Kaburaki, Naoki Yoshida, Takaki Morita), and the four values (Innovation, Quality, Transparency, Growth) in one row.
7. **Contact**: Jakarta office address (Neo Soho Mall), hours Mon–Fri 09:00–18:00, phone, email info@fts-tech.co.id, a contact form, and a WhatsApp button.
8. **Footer**: links, copyright 2026.

## Behavior

- Default language is Indonesian. The choice is stored in `localStorage`. If storage is unavailable, the page still works.
- `<html lang>` and the page title update on language change.
- Form submit builds a `mailto:` link. There is a WhatsApp button as the alternative. No data is sent to any server.
- Smooth scroll respects `prefers-reduced-motion`.

## Quality requirements

- Mobile-first. Works from 360px width with no horizontal scroll.
- Text contrast meets WCAG AA. Buttons are keyboard-focusable with visible focus.
- Avatar image is lazy-safe and has a CSS fallback background if the image fails.
- SEO basics: title, meta description, Open Graph tags, and `lang` per language.

## Inputs still needed from the user

- A real AI avatar image. A placeholder illustration is used until then.
- FTS logo file. A text wordmark "FTS" is used until then.
- Review of the English and Japanese text, which is translated from the current Indonesian/English copy.
- WhatsApp number to use for the button. The current site shows +62 895 2933 6179 and +62 811 8999 7757, so one must be chosen.

## Out of scope

Interactive AI chat, a backend, a CMS, a blog, and a careers page.

## Verification

Open the page at desktop (1440px) and mobile (375px) widths. Check every section renders, the language and currency toggles work, the hero buttons scroll to the right cards, and there are no console errors.
