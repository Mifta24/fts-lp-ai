<?php

/*
|--------------------------------------------------------------------------
| Apricot — 株式会社アプリコット / APRICOT INC. (https://ai-apricot.fts-tech.co.id)
|--------------------------------------------------------------------------
|
| Company facts are taken from https://apricot-j.co.jp (company, message and
| contact pages), read on 24 November 2026. The site publishes no email
| address and no office hours, so neither is shown. The plans, services, AI
| solutions and portfolio stay the FTS ones, presented with FTS credited as
| the technology partner (resources/brands/white-label.php).
|
*/

$shared = require __DIR__.'/../../resources/brands/white-label.php';

$name = 'Apricot';

return [
    'display_name' => $name,
    'short_name' => $name,
    'provider' => 'FTS',

    'logo' => 'images/brands/apricot/mark.png',
    'full_logo' => 'images/brands/apricot/logo.png',
    // The concierge keeps the FTS staff badge, as Mr. Yoshi asked.
    'character_dir' => 'images/character-fts-badge',

    'website' => 'https://apricot-j.co.jp',
    'social' => [],
    'show_contact_links' => true,
    'show_office_hours' => false,

    'hotel_slug' => 'fts',

    'tagline' => [
        'en' => 'Your AI concierge from Apricot for system development, websites, AI adoption and global support.',
        'ja' => 'アプリコットのAIコンシェルジュが、システム開発・Web制作・AI導入・グローバル支援のご相談をお手伝いします。',
        'id' => 'AI Concierge Apricot siap membantu kebutuhan pengembangan sistem, website, adopsi AI, dan dukungan global Anda.',
    ],

    'intro' => [
        'en' => 'Apricot has supported businesses with system development, web production and IT consulting for many years. Together with our technology partner FTS, we add AI and global support. Let me show you around.',
        'ja' => '長年のシステム開発実績に、AIとグローバル支援を加え、企業の次の成長をサポートします。技術パートナーのFTSとともに、私がご案内します。',
        'id' => 'Apricot telah lama mendukung bisnis lewat pengembangan sistem, produksi web, dan konsultasi IT. Bersama mitra teknologi kami, FTS, kami menambahkan AI dan dukungan global. Mari saya antar berkeliling.',
    ],

    'ai' => [
        'identity' => '株式会社アプリコット (APRICOT INC.), an IT company in Shinagawa, Tokyo, Japan founded in 1989 that offers business system development, web production, IT consulting and AI adoption support, and that works with its technology partner FTS (Fujiyama Technology Solutions, Jakarta) on AI automation, AI agents and offshore development',
        'context' => 'About the brand: you represent Apricot. The AI solutions, live demos and portfolio projects on this site were built by FTS, Apricot\'s technology partner: present the AI solutions as offered by Apricot together with FTS, and describe portfolio projects as FTS work. Apricot publishes no email address: point visitors to the phone number, the project request form or the contact form on apricot-j.co.jp. Website plan prices are the ones the tools return; never convert or invent prices.',
    ],

    'copy' => $shared['copy'],

    'profile' => [
        'name' => $name,
        'description' => '株式会社アプリコットは、システム開発・Web制作・ITコンサルティングの実績を活かし、企業の業務改善、AI活用、海外展開を支援するITパートナーです。技術パートナーのFTSとともに、AI自動化とAIエージェントをご提供します。',
        'translations' => [
            'ja' => ['description' => '株式会社アプリコットは、システム開発・Web制作・ITコンサルティングの実績を活かし、企業の業務改善、AI活用、海外展開を支援するITパートナーです。技術パートナーのFTSとともに、AI自動化とAIエージェントをご提供します。'],
            'en' => ['description' => 'APRICOT INC. is an IT partner that draws on its record in system development, web production and IT consulting to help companies improve operations, adopt AI and expand overseas. Together with its technology partner FTS, it offers AI automation and AI agents.'],
            'id' => ['description' => 'APRICOT INC. adalah mitra IT yang memanfaatkan pengalamannya di pengembangan sistem, produksi web, dan konsultasi IT untuk membantu perusahaan memperbaiki operasional, memanfaatkan AI, dan berekspansi ke luar negeri. Bersama mitra teknologinya, FTS, Apricot menghadirkan AI automation dan AI agent.'],
        ],
        'address' => '〒141-0021 東京都品川区上大崎3-5-3 朝日目黒ビジネスマンション 404号室',
        'city' => 'Shinagawa, Tokyo',
        'country' => 'Japan',
        'latitude' => null,
        'longitude' => null,
        'phone' => '03-6264-8940',
        'whatsapp' => null,
        'email' => null,
        'timezone' => 'Asia/Tokyo',
        'default_locale' => 'ja',
        'logo_path' => 'images/brands/apricot/mark.png',
    ],

    'knowledge' => [
        ...$shared['knowledge'],

        'Tentang Fujiyama Technology Solutions' => [
            'title' => 'Tentang Apricot',
            'body' => '株式会社アプリコット (APRICOT INC.) didirikan pada 20 April 1989 dan berkantor di Shinagawa, Tokyo, dengan modal 35 juta yen. Apricot merencanakan, merancang, dan mengembangkan sistem komputer, perangkat lunak, dan sistem web; menjual perangkat komputer; memberikan konsultasi IT; serta merancang dan membangun website. Kekuatannya: pengalaman panjang di pengembangan sistem, dukungan pemanfaatan AI untuk perbaikan operasional, serta kerja sama dengan talenta dan mitra luar negeri.',
            'translations' => [
                'en' => ['title' => 'About Apricot', 'body' => 'APRICOT INC. (株式会社アプリコット) was founded on 20 April 1989 and is based in Shinagawa, Tokyo, with capital of 35 million yen. It plans, designs and develops computer systems, software and web systems, sells computers and office equipment, provides IT consulting, and plans, designs and builds websites. Its strengths: a long record in system development, support for using AI to improve operations, and work with overseas talent and partners.'],
                'ja' => ['title' => 'アプリコットについて', 'body' => '株式会社アプリコット（APRICOT INC.）は平成元年4月20日に創立し、東京都品川区に拠点を置いています（資本金3,500万円）。コンピュータシステム・ソフトウェア・WEB系システムの企画・設計・開発、コンピュータ等の事務機器の販売、IT技術に関するコンサルティング、ホームページの企画・デザイン・制作を手がけています。長年のシステム開発実績、AI活用・業務改善への対応、海外人材・グローバル連携が強みです。'],
            ],
            'tags' => ['about', 'apricot', 'company', 'overview', '事業内容', '業務内容', '会社概要'],
        ],

        'Yoshihiro Nakagawa, Founder / President Director' => [
            'title' => '鏑木 孝和 (Takakazu Kaburaki), Direktur Perwakilan',
            'body' => '鏑木 孝和 (Takakazu Kaburaki) adalah Direktur Perwakilan (代表取締役) Apricot. Sejak berdiri, Apricot berkontribusi pada perbaikan operasional dan peningkatan nilai perusahaan lewat produksi web dan pengembangan sistem dari sudut pandang pelanggan. Kini Apricot menyambut perubahan seperti AI generatif dan pengembangan global sebagai peluang menciptakan nilai baru bersama pelanggan.',
            'translations' => [
                'en' => ['title' => '鏑木 孝和 (Takakazu Kaburaki), Representative Director', 'body' => '鏑木 孝和 (Takakazu Kaburaki) is the Representative Director (代表取締役) of Apricot. Since its founding, Apricot has helped companies improve operations and add value through web production and system development built from the customer\'s point of view. Today it treats changes such as generative AI and global development teams as chances to create new value together with its customers.'],
                'ja' => ['title' => '代表取締役 鏑木 孝和', 'body' => '株式会社アプリコットは、創業以来、お客さまの視点に立ったWEB制作・システム開発を通じて、企業の業務改善と価値向上に貢献してまいりました。近年の生成AIやデジタルツールの活用、グローバルな開発体制との連携といった変化を新しい価値創出の機会と捉え、お客さまと共に挑戦してまいります。'],
            ],
            'tags' => ['team', 'representative director', 'ceo', 'takakazu kaburaki', 'apricot'],
        ],

        'Kunjungi kantor FTS di Jakarta Barat' => [
            'title' => 'Kantor Apricot di Meguro, Tokyo',
            'body' => 'Kantor Apricot berada di 〒141-0021 東京都品川区上大崎3-5-3 朝日目黒ビジネスマンション 404号室, dekat Stasiun Meguro (JR Yamanote, Tokyu Meguro, Tokyo Metro Namboku, Toei Mita). Telepon 03-6264-8940, faks 03-6264-8941. Tidak ada parkir khusus; gunakan parkir koin di sekitar.',
            'translations' => [
                'en' => ['title' => 'The Apricot office in Meguro, Tokyo', 'body' => 'The Apricot office is at 〒141-0021 東京都品川区上大崎3-5-3 朝日目黒ビジネスマンション Room 404, near Meguro Station (JR Yamanote, Tokyu Meguro, Tokyo Metro Namboku and Toei Mita lines). Phone 03-6264-8940, fax 03-6264-8941. There is no dedicated parking; nearby coin parking is available.'],
                'ja' => ['title' => 'アプリコットのオフィス（目黒駅周辺）', 'body' => '〒141-0021 東京都品川区上大崎3-5-3 朝日目黒ビジネスマンション 404号室。JR山手線・東急目黒線・東京メトロ南北線・都営三田線の目黒駅周辺です。TEL：03-6264-8940（代表）、FAX：03-6264-8941。提携駐車場はありませんので、近隣のコインパーキングをご利用ください。'],
            ],
            'tags' => ['office', 'location', 'address', 'meguro', 'shinagawa', 'tokyo'],
            'image_url' => null,
            'details' => ['menu' => []],
        ],

        'Bagaimana cara memulai proyek dengan FTS?' => [
            'title' => 'Bagaimana cara memulai proyek dengan Apricot?',
            'body' => 'Hubungi Apricot lewat telepon 03-6264-8940, formulir kontak di apricot-j.co.jp/contact, atau kirim permintaan proyek lewat situs ini. Konsultasi awal dan penawaran harga gratis.',
            'translations' => [
                'en' => ['title' => 'How do I start a project with Apricot?', 'body' => 'Contact Apricot by phone at 03-6264-8940, through the contact form at apricot-j.co.jp/contact, or send a project request on this site. The first consultation and the quotation are free.'],
                'ja' => ['title' => 'アプリコットでプロジェクトを始めるには？', 'body' => 'お電話（03-6264-8940）、apricot-j.co.jp のお問い合わせフォーム、または本サイトのご相談フォームからご連絡ください。初回相談・お見積もりは無料です。'],
            ],
            'tags' => ['start project', 'contact', 'faq'],
        ],
    ],
];
