<?php

/*
|--------------------------------------------------------------------------
| Edepaworld — イーデパ・ワールド株式会社 (https://ai.edepaworld.co.jp)
|--------------------------------------------------------------------------
|
| Company facts are taken from https://edepaworld.co.jp (about-us.php and
| company_info.php), read on 24 November 2026. The plans, services, AI
| solutions and portfolio stay the FTS ones, presented with FTS credited as
| the technology partner (resources/brands/white-label.php).
|
*/

$shared = require __DIR__.'/../../resources/brands/white-label.php';

$name = 'Edepaworld';

return [
    'display_name' => $name,
    'short_name' => $name,
    'provider' => 'FTS',

    'logo' => 'images/brands/edepaworld/mark.png',
    'full_logo' => 'images/brands/edepaworld/logo.png',
    // The concierge keeps the FTS staff badge, as Mr. Yoshi asked.
    'character_dir' => 'images/character-fts-badge',

    'website' => 'https://edepaworld.co.jp',
    'social' => [
        'Facebook' => 'https://www.facebook.com/%E3%82%A4%E3%83%BC%E3%83%87%E3%83%91%E3%83%AF%E3%83%BC%E3%83%AB%E3%83%89%E6%A0%AA%E5%BC%8F%E4%BC%9A%E7%A4%BE-1150295341819983/',
        'X (Twitter)' => 'https://twitter.com/Edepa_World',
        'Instagram' => 'https://www.instagram.com/edepaworld',
    ],
    'show_contact_links' => true,
    // The official site publishes no office hours.
    'show_office_hours' => false,

    'hotel_slug' => 'fts',

    'tagline' => [
        'en' => 'Your AI concierge from Edepaworld for websites, apps, AI automation and AI agents.',
        'ja' => 'イーデパ・ワールドのAIコンシェルジュが、Web・アプリ制作、AI自動化、AIエージェントのご相談をお手伝いします。',
        'id' => 'AI Concierge Edepaworld siap membantu kebutuhan website, aplikasi, AI automation, dan AI agent Anda.',
    ],

    'intro' => [
        'en' => 'Edepaworld in Tokyo builds websites, apps and multilingual systems. Together with our technology partner FTS, we bring AI automation and AI agents to your business. Let me show you around.',
        'ja' => '東京のイーデパ・ワールドは、Webサイト・アプリ・多言語システムを手がけています。技術パートナーのFTSとともに、AI自動化とAIエージェントをお届けします。私がご案内します。',
        'id' => 'Edepaworld di Tokyo membangun website, aplikasi, dan sistem multibahasa. Bersama mitra teknologi kami, FTS, kami menghadirkan AI automation dan AI agent untuk bisnis Anda. Mari saya antar berkeliling.',
    ],

    'ai' => [
        'identity' => 'イーデパ・ワールド株式会社 (Edepaworld Co., Ltd.), a company in Fussa, Tokyo, Japan that builds websites, smartphone apps and multilingual translation systems and runs travel and e-commerce services, and that offers web development, AI automation and AI agent development together with its technology partner FTS (Fujiyama Technology Solutions, Jakarta)',
        'context' => 'About the brand: you represent Edepaworld. The AI solutions, live demos and portfolio projects on this site were built by FTS, Edepaworld\'s technology partner: present the AI solutions as offered by Edepaworld together with FTS, and describe portfolio projects as FTS work. Website plan prices are the ones the tools return; never convert or invent prices.',
    ],

    'copy' => $shared['copy'],

    'profile' => [
        'name' => $name,
        'description' => 'イーデパ・ワールド株式会社は、東京都福生市の企業です。Webサイト・スマホアプリ制作、多言語自動翻訳システム、旅行・通販サービスの運営を手がけ、技術パートナーのFTSとともにAI自動化とAIエージェントをご提供します。',
        'translations' => [
            'ja' => ['description' => 'イーデパ・ワールド株式会社は、東京都福生市の企業です。Webサイト・スマホアプリ制作、多言語自動翻訳システム、旅行・通販サービスの運営を手がけ、技術パートナーのFTSとともにAI自動化とAIエージェントをご提供します。'],
            'en' => ['description' => 'Edepaworld Co., Ltd. is a company in Fussa, Tokyo that builds websites, smartphone apps and multilingual translation systems and runs travel and e-commerce services. Together with its technology partner FTS, it offers AI automation and AI agents.'],
            'id' => ['description' => 'Edepaworld Co., Ltd. adalah perusahaan di Fussa, Tokyo yang membangun website, aplikasi smartphone, dan sistem terjemahan multibahasa serta menjalankan layanan perjalanan dan e-commerce. Bersama mitra teknologinya, FTS, Edepaworld menghadirkan AI automation dan AI agent.'],
        ],
        'address' => '〒197-0012 東京都福生市加美平',
        'city' => 'Fussa, Tokyo',
        'country' => 'Japan',
        'latitude' => null,
        'longitude' => null,
        'phone' => '042-593-0707',
        'whatsapp' => null,
        'email' => 'info@edepaworld.co.jp',
        'timezone' => 'Asia/Tokyo',
        'default_locale' => 'ja',
        'logo_path' => 'images/brands/edepaworld/mark.png',
    ],

    'knowledge' => [
        ...$shared['knowledge'],

        'Tentang Fujiyama Technology Solutions' => [
            'title' => 'Tentang Edepaworld',
            'body' => 'イーデパ・ワールド株式会社 (Edepaworld Co., Ltd.) didirikan pada 3 Maret 2014 dan berkantor pusat di Fussa, Tokyo. Edepaworld menjalankan situs e-commerce dan layanan informasi restoran dan perjalanan, serta membuat website, aplikasi smartphone, dan layanan pengolahan informasi, termasuk sistem terjemahan otomatis multibahasa. Bersama mitra teknologinya, FTS, Edepaworld menghadirkan AI automation dan AI agent untuk bisnis.',
            'translations' => [
                'en' => ['title' => 'About Edepaworld', 'body' => 'Edepaworld Co., Ltd. (イーデパ・ワールド株式会社) was founded on 3 March 2014 and is headquartered in Fussa, Tokyo. It runs e-commerce sites and restaurant and travel information services, and builds websites, smartphone apps and information processing services, including a multilingual automatic translation system. Together with its technology partner FTS, Edepaworld brings AI automation and AI agents to businesses.'],
                'ja' => ['title' => 'イーデパ・ワールドについて', 'body' => 'イーデパ・ワールド株式会社は平成26年3月3日に設立され、東京都福生市に本社を置いています。インターネット通販サイトの運営、レストランや旅行の情報提供サービス、ホームページ制作、スマホアプリ制作及び情報処理サービスを手がけ、多言語自動翻訳システムも開発しています。技術パートナーのFTSとともに、AI自動化とAIエージェントを企業にお届けします。'],
            ],
            'tags' => ['about', 'edepaworld', 'company', 'overview', '事業内容', '業務内容', '会社概要'],
        ],

        'Yoshihiro Nakagawa, Founder / President Director' => [
            'title' => '井梅 光人, Direktur Perwakilan',
            'body' => '井梅 光人 adalah Direktur Perwakilan (代表取締役) Edepaworld. Pesannya: perubahan zaman bergerak semakin cepat, dan Edepaworld ingin terus melangkah lebih jauh untuk menyediakan informasi dan sistem bagi semua orang. Prioritas utama perusahaan adalah kesuksesan pelanggan dan cara berpikir jangka panjang.',
            'translations' => [
                'en' => ['title' => '井梅 光人, Representative Director', 'body' => '井梅 光人 is the Representative Director (代表取締役) of Edepaworld. His message: the pace of change keeps accelerating, and Edepaworld wants to keep moving ahead to provide information and systems to everyone. The company puts its customers\' success first and thinks long term.'],
                'ja' => ['title' => '代表取締役 井梅 光人', 'body' => 'イーデパ・ワールド株式会社 代表取締役の井梅光人です。昨今の時代の流れは加速の一途をたどっています。弊社もこの時代の流れに乗り、より先へ進み、皆様へ情報やシステムをご提供してまいります。お客様の成功を最優先に、長期的な視点で取り組んでいます。'],
            ],
            'tags' => ['team', 'representative director', 'ceo', 'edepaworld'],
        ],

        'Kunjungi kantor FTS di Jakarta Barat' => [
            'title' => 'Kantor pusat Edepaworld di Fussa, Tokyo',
            'body' => 'Kantor pusat Edepaworld berada di 〒197-0012 東京都福生市加美平 (Kamidaira, Fussa, Tokyo). Telepon 042-593-0707, email info@edepaworld.co.jp. Hubungi kami sebelum berkunjung.',
            'translations' => [
                'en' => ['title' => 'Edepaworld head office in Fussa, Tokyo', 'body' => 'The Edepaworld head office is at 〒197-0012 東京都福生市加美平 (Kamidaira, Fussa, Tokyo). Phone 042-593-0707, email info@edepaworld.co.jp. Please contact us before visiting.'],
                'ja' => ['title' => 'イーデパ・ワールド本社（東京都福生市）', 'body' => '本社は〒197-0012 東京都福生市加美平にあります。電話 042-593-0707、メール info@edepaworld.co.jp。ご来社の際は事前にご連絡ください。'],
            ],
            'tags' => ['office', 'location', 'address', 'fussa', 'tokyo'],
            'image_url' => null,
            'details' => ['menu' => []],
        ],

        'Bagaimana cara memulai proyek dengan FTS?' => [
            'title' => 'Bagaimana cara memulai proyek dengan Edepaworld?',
            'body' => 'Hubungi Edepaworld lewat telepon 042-593-0707 atau email info@edepaworld.co.jp, atau kirim permintaan proyek lewat situs ini. Tim kami bersama mitra teknologi FTS akan menindaklanjutinya.',
            'translations' => [
                'en' => ['title' => 'How do I start a project with Edepaworld?', 'body' => 'Contact Edepaworld by phone at 042-593-0707 or by email at info@edepaworld.co.jp, or send a project request on this site. Our team and our technology partner FTS will follow up.'],
                'ja' => ['title' => 'イーデパ・ワールドでプロジェクトを始めるには？', 'body' => 'お電話（042-593-0707）、メール（info@edepaworld.co.jp）、または本サイトのご相談フォームからご連絡ください。弊社と技術パートナーのFTSが対応いたします。'],
            ],
            'tags' => ['start project', 'contact', 'faq'],
        ],
    ],
];
