<?php

/*
|--------------------------------------------------------------------------
| FTS — Fujiyama Technology Solutions (default brand)
|--------------------------------------------------------------------------
|
| The original site. Its company record and knowledge items come from
| FtsSeeder, so this brand leaves the database alone (no profile, no
| knowledge overrides) and every page reads exactly as it did before.
|
*/

return [
    'display_name' => 'Fujiyama Technology Solutions',
    'short_name' => 'FTS',
    'provider' => 'FTS',

    'logo' => 'images/fts-mark.png',
    'full_logo' => 'images/logo.webp',
    'character_dir' => 'images/character',

    'website' => 'https://fts-tech.co.id',
    'social' => [],
    'show_contact_links' => false,
    'show_office_hours' => true,

    'hotel_slug' => 'fts',

    'tagline' => [
        'en' => 'Your AI concierge for web development, AI automation, and offshore development.',
        'ja' => 'AIコンシェルジュが、Web開発・AI自動化・オフショア開発のご相談をお手伝いします。',
        'id' => 'AI Concierge siap membantu kebutuhan web development, AI automation, dan offshore development Anda.',
    ],

    'intro' => [
        'en' => 'Japanese quality, Indonesian excellence. Web development and AI automation for businesses in Indonesia. Let me show you around.',
        'ja' => '日本の品質と、インドネシアの力。インドネシアの企業に向けたWeb開発とAI自動化を、私がご案内します。',
        'id' => 'Kualitas Jepang, keunggulan Indonesia. Web development dan AI automation untuk bisnis di Indonesia. Mari saya antar berkeliling.',
    ],

    'ai' => [
        'identity' => 'an IT company in :city, :country that offers web development, AI automation, AI agent development and offshore development',
        'context' => '',
    ],

    // Interface copy that differs beyond the name: [group => [locale => [key => text]]].
    'copy' => [],

    // Columns of the company record set by `php artisan brand:apply`.
    'profile' => [],

    // Knowledge items keyed by the FTS title they stand in for: null switches
    // the item off, ['rename' => true] swaps "FTS" for the brand's short name,
    // and a full item (title, body, translations, tags) replaces it.

    'knowledge' => [],
];
