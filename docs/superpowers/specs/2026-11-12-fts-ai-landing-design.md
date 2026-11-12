# FTS AI Landing Page — Design Spec (Laravel version)

Date: 2026-11-12
Replaces: https://fts-tech.co.id/ (company profile)
Layout reference: https://hotel-ai.fts-tech.co.id/ (immersive avatar hero, ID/EN/JA switch)
Theme reference: https://fts-tech.co.id/ (colors, fonts, hero style)
Base code: Mifta24/fts-hotel-ai, stripped down. All hotel, admin, booking, member, loyalty, and AI chat code is removed.

## Goal

Turn the FTS company profile into an AI-focused landing page. Visitors should understand that FTS sells AI solutions and reach an inquiry (WhatsApp, form, email).

## Decisions

- Format: hybrid. An immersive hero with an AI avatar, then scrollable sections.
- Avatar: visual only, plus a greeting. No chat, no backend, no AI API. The avatar is the cut-out image from the hotel repo (`public/images/character/character greeting.webp`).
- Languages: Indonesian (default), English, Japanese.
- Stack: Laravel 13 (PHP 8.4), Vite 8, Tailwind CSS 4, vanilla JS. One route (`/`). No database.
- Theme: taken from the live fts-tech.co.id (see below). The earlier dark-green idea is dropped.

## Theme (from the live site's CSS)

| Token | Value |
|-------|-------|
| Primary blue | `hsl(203 89% 53%)` (`#1CA0F2`), light `hsl(213 84% 63%)`, dark `hsl(217 91% 35%)`, darker `hsl(224 76% 25%)` |
| Text on light | `hsl(222.2 84% 4.9%)` |
| Muted text | `hsl(215.4 16.3% 45%)` |
| Border | `hsl(214.3 31.8% 91.4%)` |
| Hero background | `#030712` with a `radial-gradient(125% 125% at 50% 0%, #020617 50%, blue)`, a starfield canvas, and a blue glow rising from the bottom |
| Section background | white, alternating with `rgba(225,231,239,.3)` |
| Cards | `linear-gradient(145deg, #fff, #eef2f6)`, border `rgba(226,232,240,.5)`, radius 12px, shadow `0 4px 20px -4px rgba(28,160,242,.1)` |
| Header | fixed, `rgba(255,255,255,.8)`, blur 4px, active link underlined in blue |
| Font | Inter (400–800). Headings bold. Hero headline last line in a light-blue to blue gradient |
| Buttons | pill shaped. Primary is white with blue text on the hero. Secondary is translucent gray |

## Page structure

1. Header: logo ("Fujiyama" plus the round FTS logo), nav (Home, About, Services, Pricing, Team, Contact), ID/EN/JA switch, WhatsApp button.
2. Hero (100vh): starfield, badge "Japanese Quality • Indonesian Excellence", headline, intro, six feature tiles (Web Development, AI Automation, AI Agent, Offshore, Japanese Quality, Business Automation), two buttons, and the avatar on the right side on desktop.
3. About, services (four cards with the restaurant website as a case-study line), how it works (3 steps), pricing (5, 10, 15 pages, quote; IDR/USD toggle), team and values, contact (address, hours, form, WhatsApp), footer.

## Behavior

- Language: `?lang=id|en|ja`, stored in a cookie. Unknown values fall back to `id`. Text comes from Laravel lang files, so pages render without JavaScript.
- Pricing: server renders IDR. A JS toggle switches to USD at `FTS_USD_RATE` (16500).
- Contact form: opens the visitor's mail app through `mailto:`. Nothing is sent to a server. Empty required fields are flagged.
- Starfield: canvas animation that is paused when `prefers-reduced-motion` is set.

## Quality requirements

- Works from 360px width with no horizontal scroll. Contrast meets WCAG AA. Visible keyboard focus.
- Hero still reads well if the avatar image fails to load.
- SEO: title, description, Open Graph tags, `lang` on `<html>`.

## Out of scope

AI chat, database, admin, booking, CMS, blog.
