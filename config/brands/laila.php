<?php

/*
|--------------------------------------------------------------------------
| Office Laila — オフィス らいら / OFFICE LAILA (https://ai.office-laila.biz)
|--------------------------------------------------------------------------
|
| Company facts are taken from the Office Laila WordPress theme
| (github.com/Rizz404/office-laila-wp-theme, page-company.php and
| page-message.php at commit 8168058), read on 25 November 2026: the
| office-laila.biz site itself was unreachable that day (DNS). The site
| publishes no email address and no office hours, so neither is shown.
| The plans, services, AI solutions and portfolio stay the FTS ones,
| presented with FTS credited as the technology partner
| (resources/brands/white-label.php).
|
*/

$shared = require __DIR__.'/../../resources/brands/white-label.php';

$name = 'Office Laila';

return [
    'display_name' => $name,
    'short_name' => $name,
    'provider' => 'FTS',

    'logo' => 'images/brands/laila/mark.png',
    'full_logo' => 'images/brands/laila/logo.png',
    // The concierge keeps the FTS staff badge, as Mr. Yoshi asked.
    'character_dir' => 'images/character-fts-badge',

    'website' => 'https://office-laila.biz',
    'social' => [],
    'show_contact_links' => true,
    'show_office_hours' => false,

    'hotel_slug' => 'fts',

    'tagline' => [
        'en' => 'Your AI concierge from Office Laila for system development, websites, AI adoption and global support.',
        'ja' => 'オフィス らいらのAIコンシェルジュが、システム開発・Web制作・AI導入・グローバル支援のご相談をお手伝いします。',
        'id' => 'AI Concierge Office Laila siap membantu kebutuhan pengembangan sistem, website, adopsi AI, dan dukungan global Anda.',
    ],

    'intro' => [
        'en' => 'Office Laila supports businesses with system development, web production and IT consulting. Together with our technology partner FTS, we add AI and global support. Let me show you around.',
        'ja' => '長年のシステム開発実績に、AIとグローバル支援を加え、企業の次の成長をサポートします。技術パートナーのFTSとともに、私がご案内します。',
        'id' => 'Office Laila mendukung bisnis lewat pengembangan sistem, produksi web, dan konsultasi IT. Bersama mitra teknologi kami, FTS, kami menambahkan AI dan dukungan global. Mari saya antar berkeliling.',
    ],

    'ai' => [
        'identity' => 'オフィス らいら (OFFICE LAILA), an IT company in Iwatsuki, Saitama, Japan that offers system and software development, web production, IT consulting and AI adoption support, and that works with its technology partner FTS (Fujiyama Technology Solutions, Jakarta) on AI automation, AI agents and offshore development',
        'context' => 'About the brand: you represent Office Laila (オフィス らいら). The AI solutions, live demos and portfolio projects on this site were built by FTS, Office Laila\'s technology partner: present the AI solutions as offered by Office Laila together with FTS, and describe portfolio projects as FTS work. Office Laila publishes no email address: point visitors to the phone number, the project request form or the contact form on office-laila.biz. Website plan prices are the ones the tools return; never convert or invent prices.',
    ],

    'copy' => $shared['copy'],

    'profile' => [
        'name' => $name,
        'description' => 'オフィス らいらは、システム開発・Web制作・ITコンサルティングの実績を活かし、企業の業務改善、AI活用、海外展開を支援するITパートナーです。技術パートナーのFTSとともに、AI自動化とAIエージェントをご提供します。',
        'translations' => [
            'ja' => ['description' => 'オフィス らいらは、システム開発・Web制作・ITコンサルティングの実績を活かし、企業の業務改善、AI活用、海外展開を支援するITパートナーです。技術パートナーのFTSとともに、AI自動化とAIエージェントをご提供します。'],
            'en' => ['description' => 'OFFICE LAILA is an IT partner that draws on its record in system development, web production and IT consulting to help companies improve operations, adopt AI and expand overseas. Together with its technology partner FTS, it offers AI automation and AI agents.'],
            'id' => ['description' => 'OFFICE LAILA adalah mitra IT yang memanfaatkan pengalamannya di pengembangan sistem, produksi web, dan konsultasi IT untuk membantu perusahaan memperbaiki operasional, memanfaatkan AI, dan berekspansi ke luar negeri. Bersama mitra teknologinya, FTS, Office Laila menghadirkan AI automation dan AI agent.'],
        ],
        'address' => '埼玉県岩槻区金重',
        'city' => 'Iwatsuki, Saitama',
        'country' => 'Japan',
        'latitude' => null,
        'longitude' => null,
        'phone' => '090-3905-5695',
        'whatsapp' => null,
        'email' => null,
        'timezone' => 'Asia/Tokyo',
        'default_locale' => 'ja',
        'logo_path' => 'images/brands/laila/mark.png',
    ],

    'knowledge' => [
        ...$shared['knowledge'],

        'Tentang Fujiyama Technology Solutions' => [
            'title' => 'Tentang Office Laila',
            'body' => 'オフィス らいら (OFFICE LAILA) didirikan pada 20 April 1989 dan berlokasi di Iwatsuki, Saitama, dengan modal 3 juta yen. Office Laila merencanakan, merancang, dan mengembangkan sistem komputer, perangkat lunak, dan sistem web; menjual perangkat komputer; memberikan konsultasi IT; serta merancang dan membangun website. Kekuatannya: pengalaman panjang di pengembangan sistem, dukungan pemanfaatan AI untuk perbaikan operasional, serta kerja sama dengan talenta dan mitra luar negeri.',
            'translations' => [
                'en' => ['title' => 'About Office Laila', 'body' => 'OFFICE LAILA (オフィス らいら) was founded on 20 April 1989 and is based in Iwatsuki, Saitama, with capital of 3 million yen. It plans, designs and develops computer systems, software and web systems, sells computers and office equipment, provides IT consulting, and plans, designs and builds websites. Its strengths: a long record in system development, support for using AI to improve operations, and work with overseas talent and partners.'],
                'ja' => ['title' => 'オフィス らいらについて', 'body' => 'オフィス らいら（OFFICE LAILA）は平成元年4月20日に創立し、埼玉県岩槻区金重に拠点を置いています（資本金300万円）。コンピュータシステム・ソフトウェア・WEB系システムの企画・調査・設計・開発、コンピュータその他の事務機器の販売、IT技術に関するコンサルティング、ホームページの企画・デザイン・制作・開発を手がけています。長年のシステム開発実績、AI活用・業務改善への対応、海外人材・グローバル連携が強みです。'],
            ],
            'tags' => ['about', 'office laila', 'laila', 'らいら', 'company', 'overview', '事業内容', '業務内容', '会社概要'],
        ],

        'Yoshihiro Nakagawa, Founder / President Director' => [
            'title' => '盛田 貴樹 (Takaki Morita), Direktur Perwakilan',
            'body' => '盛田 貴樹 (Takaki Morita) adalah Direktur Perwakilan (代表取締役) Office Laila. Sejak berdiri, Office Laila berkontribusi pada perbaikan operasional dan peningkatan nilai perusahaan lewat produksi web dan pengembangan sistem dari sudut pandang pelanggan. Kini Office Laila menyambut perubahan seperti AI generatif dan pengembangan global sebagai peluang menciptakan nilai baru bersama pelanggan.',
            'translations' => [
                'en' => ['title' => '盛田 貴樹 (Takaki Morita), Representative Director', 'body' => '盛田 貴樹 (Takaki Morita) is the Representative Director (代表取締役) of Office Laila. Since its founding, Office Laila has helped companies improve operations and add value through web production and system development built from the customer\'s point of view. Today it treats changes such as generative AI and global development teams as chances to create new value together with its customers.'],
                'ja' => ['title' => '代表取締役 盛田 貴樹', 'body' => 'オフィス らいらは、創業以来、お客さまの視点に立ったWEB制作・システム開発を通じて、企業の業務改善と価値向上に貢献してまいりました。近年の生成AIやデジタルツールの活用、グローバルな開発体制との連携といった変化を新しい価値創出の機会と捉え、お客さまと共に挑戦してまいります。'],
            ],
            'tags' => ['team', 'representative director', 'ceo', 'takaki morita', 'office laila'],
        ],

        'Kunjungi kantor FTS di Jakarta Barat' => [
            'title' => 'Kantor Office Laila di Iwatsuki, Saitama',
            'body' => 'Office Laila berlokasi di 埼玉県岩槻区金重 (Kanashige, Iwatsuki, Saitama). Telepon 090-3905-5695, faks 03-6264-8941. Hubungi kami sebelum berkunjung.',
            'translations' => [
                'en' => ['title' => 'The Office Laila office in Iwatsuki, Saitama', 'body' => 'Office Laila is located at 埼玉県岩槻区金重 (Kanashige, Iwatsuki, Saitama). Phone 090-3905-5695, fax 03-6264-8941. Please contact us before visiting.'],
                'ja' => ['title' => 'オフィス らいらの所在地（埼玉県岩槻区）', 'body' => '所在地は埼玉県岩槻区金重です。TEL：090-3905-5695、FAX：03-6264-8941。ご来社の際は事前にご連絡ください。'],
            ],
            'tags' => ['office', 'location', 'address', 'iwatsuki', 'saitama', '所在地'],
            'image_url' => null,
            'details' => ['menu' => []],
        ],

        'Bagaimana cara memulai proyek dengan FTS?' => [
            'title' => 'Bagaimana cara memulai proyek dengan Office Laila?',
            'body' => 'Hubungi Office Laila lewat telepon 090-3905-5695, formulir kontak di office-laila.biz/contact, atau kirim permintaan proyek lewat situs ini. Konsultasi awal dan penawaran harga gratis.',
            'translations' => [
                'en' => ['title' => 'How do I start a project with Office Laila?', 'body' => 'Contact Office Laila by phone at 090-3905-5695, through the contact form at office-laila.biz/contact, or send a project request on this site. The first consultation and the quotation are free.'],
                'ja' => ['title' => 'オフィス らいらでプロジェクトを始めるには？', 'body' => 'お電話（090-3905-5695）、office-laila.biz のお問い合わせフォーム、または本サイトのご相談フォームからご連絡ください。初回相談・お見積もりは無料です。'],
            ],
            'tags' => ['start project', 'contact', 'faq'],
        ],
    ],
];
