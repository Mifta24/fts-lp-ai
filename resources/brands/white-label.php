<?php

/*
|--------------------------------------------------------------------------
| Shared content for brands that present the FTS solutions as a partner
|--------------------------------------------------------------------------
|
| Used by config/brands/edepaworld.php and config/brands/apricot.php. The
| website plans, services, AI solutions and portfolio stay the FTS ones;
| what changes is who presents them. ":brand" is the brand's short name and
| ":provider" is FTS, the technology partner that built the solutions.
|
*/

return [

    // Interface copy that would otherwise call FTS's portfolio the brand's own work.
    'copy' => [
        'narration' => [
            'en' => [
                'portfolio' => 'Here is the portfolio of our technology partner :provider. It has :count projects, including :examples. Choose one to read about it, or ask me anything.',
                'portfolio_one' => 'Here is a project by our technology partner :provider: :examples. Open it to read about it, or ask me anything.',
                'tour_portfolio' => 'Come, let me show you projects by our technology partner :provider.',
            ],
            'id' => [
                'portfolio' => 'Inilah portofolio mitra teknologi kami, :provider. Ada :count proyek, di antaranya :examples. Pilih salah satu untuk membacanya, atau tanyakan apa saja kepada saya.',
                'portfolio_one' => 'Inilah proyek mitra teknologi kami, :provider: :examples. Buka untuk membacanya, atau tanyakan apa saja kepada saya.',
                'tour_portfolio' => 'Mari, saya tunjukkan proyek-proyek mitra teknologi kami, :provider.',
            ],
            'ja' => [
                'portfolio' => 'こちらは技術パートナー:providerの制作実績です。:count件、:examples などがあります。選ぶと詳細をご覧いただけます。ご質問もどうぞ。',
                'portfolio_one' => '技術パートナー:providerの制作実績、:examples です。詳細をご覧ください。ご質問もどうぞ。',
                'tour_portfolio' => 'こちらへどうぞ。技術パートナー:providerの制作実績をご紹介します。',
            ],
        ],
        'labels' => [
            'en' => ['menu_portfolio_q' => 'Can I see the projects :provider has built?'],
            'id' => ['menu_portfolio_q' => 'Boleh saya lihat proyek yang pernah dibuat :provider?'],
            'ja' => ['menu_portfolio_q' => ':providerがこれまでに手がけたプロジェクトを見せてください。'],
        ],
    ],

    // Company items of FTS that do not belong to the brand. Keys are the FTS titles.
    'knowledge' => [
        // FTS's own team, values and office hours are not the brand's.
        'Naoki Yoshida, Director' => null,
        'Takaki Morita, Director' => null,
        'Nilai-nilai FTS' => null,
        'Jam kerja dan waktu respons' => null,

        // Service copy where the brand simply takes FTS's place.
        'Otomasi Bisnis & Digital Marketing' => ['rename' => true],
        'Proyek lebih dari 15 halaman' => ['rename' => true],
        'Apakah website bisa terhubung dengan WhatsApp?' => ['rename' => true],

        // The offshore team is FTS's: say so rather than claim it.
        'Apakah FTS menyediakan tim offshore untuk perusahaan Jepang?' => [
            'title' => 'Apakah :brand menyediakan tim offshore untuk perusahaan Jepang?',
            'body' => 'Ya. Bersama mitra teknologinya, :provider (Fujiyama Technology Solutions) di Jakarta, :brand menyediakan tim developer khusus di Indonesia untuk perusahaan Jepang, dengan proses QA standar Jepang, koordinasi proyek dua bahasa, dan skala tim yang fleksibel.',
            'translations' => [
                'en' => ['title' => 'Does :brand provide offshore teams for Japanese companies?', 'body' => 'Yes. Together with its technology partner :provider (Fujiyama Technology Solutions) in Jakarta, :brand provides dedicated developer teams in Indonesia for Japanese companies, with a Japanese-standard QA process, bilingual project coordination, and flexible team scaling.'],
                'ja' => ['title' => '日本企業向けのオフショアチームはありますか？', 'body' => 'はい。:brandは、ジャカルタの技術パートナー:provider（Fujiyama Technology Solutions）と連携し、日本基準のQAプロセス、バイリンガルのプロジェクト調整、柔軟なチーム拡張を備えたインドネシアの専任開発チームをご提供します。'],
            ],
            'tags' => ['offshore', 'japan', 'faq', 'partner'],
        ],

        // Who built the AI solutions and the portfolio.
        'Takakazu Kaburaki, Chief Operating Officer' => [
            'title' => 'Mitra teknologi: :provider',
            'body' => 'Solusi AI, demo live, dan proyek di portofolio situs ini dibuat oleh :provider (Fujiyama Technology Solutions), perusahaan IT berakar Jepang di Jakarta yang menjadi mitra teknologi :brand untuk web development, AI automation, AI agent, dan offshore development.',
            'translations' => [
                'en' => ['title' => 'Technology partner: :provider', 'body' => 'The AI solutions, live demos and portfolio projects on this site are built by :provider (Fujiyama Technology Solutions), a Japanese-rooted IT company in Jakarta and the technology partner of :brand for web development, AI automation, AI agents and offshore development.'],
                'ja' => ['title' => '技術パートナー：:provider', 'body' => '本サイトのAIソリューション、ライブデモ、制作実績は、ジャカルタの日系IT企業:provider（Fujiyama Technology Solutions）が開発しました。:providerは、Web開発、AI自動化、AIエージェント、オフショア開発における:brandの技術パートナーです。'],
            ],
            'tags' => ['partner', 'fts', 'ai solutions', 'portfolio', 'technology partner'],
        ],
    ],
];
