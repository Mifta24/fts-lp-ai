<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\HotelKnowledgeItem;
use App\Models\RoomInventory;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Fills the concierge app with the content of https://fts-tech.co.id/.
 *
 * The app was built for hotels, so the company data is placed in the same
 * places: the company is the "hotel", the website pricing plans are the "room
 * types", the services are "facilities", and the team, values, policies and
 * FAQ are knowledge items. Images are the ones the FTS site uses.
 */
class FtsSeeder extends Seeder
{
    private const IMG = 'https://images.unsplash.com/%s?auto=format&fit=crop&w=%d&q=80';

    public function run(): void
    {
        [$email, $password, $generated] = $this->ownerLogin();

        $owner = User::firstOrCreate(
            ['email' => $email],
            ['name' => 'Owner', 'password' => Hash::make($password), 'email_verified_at' => now()]
        );

        $hotel = Hotel::updateOrCreate(
            ['slug' => 'fts'],
            [
                'name' => 'Fujiyama Technology Solutions',
                'logo_path' => 'images/fts-mark.png',
                'description' => 'Perusahaan IT berakar Jepang di Jakarta yang menghadirkan web development, AI automation, AI agent, offshore development, dan dukungan digital marketing untuk bisnis di Indonesia.',
                'translations' => [
                    'id' => ['description' => 'Perusahaan IT berakar Jepang di Jakarta yang menghadirkan web development, AI automation, AI agent, offshore development, dan dukungan digital marketing untuk bisnis di Indonesia.'],
                    'en' => ['description' => 'A Japanese-rooted IT company in Jakarta delivering web development, AI automation, AI agents, offshore development, and digital marketing support for businesses in Indonesia.'],
                    'ja' => ['description' => 'ジャカルタ拠点の日系IT企業。インドネシアの企業向けに、Web開発、AI自動化、AIエージェント、オフショア開発、デジタルマーケティング支援を提供します。'],
                ],
                'address' => 'Neo Soho Mall, Jalan Let. Jend. S. Parman Kav. 28 Unit 2011, Tanjung Duren Selatan, Grogol Petamburan',
                'city' => 'West Jakarta, DKI Jakarta 11470',
                'country' => 'Indonesia',
                'latitude' => null,
                'longitude' => null,
                'phone' => '+62 895 2933 6179',
                'whatsapp' => '6289529336179',
                'email' => 'info@fts-tech.co.id',
                'timezone' => 'Asia/Jakarta',
                'currency' => 'IDR',
                'default_locale' => 'id',
                // Office hours, Monday to Friday. The stage shows them where a hotel shows check-in and check-out.
                'check_in_time' => '09:00',
                'check_out_time' => '18:00',
                'cover_path' => sprintf(self::IMG, 'photo-1461749280684-dccba630e2f6', 1920),
                'public_status' => 'published',
            ]
        );

        $hotel->users()->syncWithoutDetaching([
            $owner->id => ['role' => 'owner', 'status' => 'active'],
        ]);

        $sort = 0;
        foreach ($this->plans() as $plan) {
            $image = $plan['image'];
            unset($plan['image']);

            $roomType = $hotel->roomTypes()->updateOrCreate(
                ['slug' => $plan['slug']],
                [
                    ...$plan,
                    'size_sqm' => null,
                    'max_adults' => 5,
                    'max_children' => 0,
                    'bed_config' => [],
                    'view_type' => null,
                    'breakfast_included' => false,
                    'extra_bed_available' => false,
                    'extra_bed_price' => null,
                    'amenities' => [],
                    'sort_order' => $sort++,
                    'is_active' => true,
                ]
            );

            $roomType->images()->updateOrCreate(
                ['image_url' => $image['url']],
                ['tags' => ['website'], 'alt_text' => $image['alt'], 'sort_order' => 0]
            );
            $roomType->images()->where('image_url', '!=', $image['url'])->delete();

            $this->seedInventory($roomType, 20);
        }

        $this->seedKnowledgeBase($hotel);

        if ($owner->wasRecentlyCreated && ($generated || ! app()->isProduction())) {
            $this->command?->info("Admin login: {$email} — password: {$password}".($generated ? ' (generated: save it now, it is not shown again)' : ''));
        }
    }

    /**
     * The admin's email and password. Outside production the demo password
     * is fine; in production it comes from FTS_OWNER_PASSWORD or is a fresh
     * random one, so a live site never has a guessable login.
     *
     * @return array{0: string, 1: string, 2: bool}
     */
    private function ownerLogin(): array
    {
        $email = (string) config('services.fts_owner.email');
        $configured = config('services.fts_owner.password');

        if (filled($configured)) {
            return [$email, (string) $configured, false];
        }

        return app()->isProduction() ? [$email, Str::password(20), true] : [$email, 'password', false];
    }

    /** @return array<int, array<string, mixed>> */
    private function plans(): array
    {
        $contract = [
            'id' => 'Desain & pengembangan website gratis. Biaya server %s per bulan, kontrak 36 bulan (6 bulan terakhir gratis).',
            'en' => 'Website design and development are free. Server fee %s per month on a 36-month contract (last 6 months free).',
            'ja' => 'Webサイトの設計・開発は無料。サーバー費用は月額%s、36か月契約（最後の6か月は無料）。',
        ];

        $make = function (int $pages, int $price, string $priceText, string $imageId, string $alt) use ($contract) {
            $name = [
                'id' => "Website Hingga {$pages} Halaman",
                'en' => "Website up to {$pages} pages",
                'ja' => "Webサイト {$pages}ページまで",
            ];
            $description = [
                'id' => "Paket website hingga {$pages} halaman. ".sprintf($contract['id'], $priceText),
                'en' => "Website package of up to {$pages} pages. ".sprintf($contract['en'], $priceText),
                'ja' => "{$pages}ページまでのWebサイトプラン。".sprintf($contract['ja'], $priceText),
            ];

            return [
                'slug' => "website-{$pages}-pages",
                'name' => $name['id'],
                'description' => $description['id'],
                'translations' => [
                    'id' => ['name' => $name['id'], 'description' => $description['id']],
                    'en' => ['name' => $name['en'], 'description' => $description['en']],
                    'ja' => ['name' => $name['ja'], 'description' => $description['ja']],
                ],
                'base_price' => $price,
                'image' => ['url' => sprintf(self::IMG, $imageId, 1200), 'alt' => $alt],
            ];
        };

        return [
            $make(5, 700000, 'Rp 700.000', 'photo-1461749280684-dccba630e2f6', 'Paket website hingga 5 halaman'),
            $make(10, 1200000, 'Rp 1.200.000', 'photo-1551288049-bebda4e38f71', 'Paket website hingga 10 halaman'),
            $make(15, 1600000, 'Rp 1.600.000', 'photo-1521737604893-d14cc237f11d', 'Paket website hingga 15 halaman'),
        ];
    }

    /**
     * Slots stay open every day. The quote adds up one price per day, so each
     * day carries one thirtieth of the monthly fee, and a 30-day period costs
     * about one month. A website plan has no weekend surcharge. Some slots
     * are taken, but never more than half, so a plan is always open to book.
     */
    private function seedInventory(RoomType $roomType, int $totalUnits): void
    {
        $start = now()->startOfDay();

        for ($i = 0; $i < 120; $i++) {
            RoomInventory::updateOrCreate(
                ['room_type_id' => $roomType->id, 'stay_date' => $start->copy()->addDays($i)],
                [
                    'total_units' => $totalUnits,
                    'booked_units' => ($i * 5 + $roomType->id * 3) % (intdiv($totalUnits, 2) + 1),
                    'price' => round((float) $roomType->base_price / 30),
                ]
            );
        }
    }

    private function seedKnowledgeBase(Hotel $hotel): void
    {
        $sort = 0;

        foreach ($this->knowledgeItems() as $item) {
            $hotel->knowledgeItems()->updateOrCreate(
                ['title' => $item['title']],
                [
                    'category' => $item['category'],
                    'body' => $item['body'],
                    'translations' => $item['translations'],
                    'tags' => $item['tags'],
                    'image_url' => $item['image_url'] ?? null,
                    'images' => $item['images'] ?? null,
                    'details' => $item['details'] ?? null,
                    'is_active' => true,
                    'sort_order' => $sort++,
                ]
            );
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function knowledgeItems(): array
    {
        return [
            ...$this->aboutItems(),
            ...$this->serviceItems(),
            ...$this->aiDemoItems(),
            ...$this->portfolioItems(),
            ...$this->officeItems(),
            ...$this->policyItems(),
            ...$this->faqItems(),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function aboutItems(): array
    {
        return [
            [
                'category' => HotelKnowledgeItem::CATEGORY_GENERAL,
                'title' => 'Tentang Fujiyama Technology Solutions',
                'body' => 'Fujiyama Technology Solutions (FTS) adalah perusahaan IT berakar Jepang di Indonesia yang membantu bisnis mengubah website, AI automation, dan offshore development menjadi inquiry yang terukur. FTS memadukan standar kualitas Jepang dengan pemahaman pasar Indonesia: struktur website ramah SEO, integrasi WhatsApp, paket bulanan yang terjangkau, otomasi bisnis, dan dukungan digital marketing.',
                'translations' => [
                    'en' => ['title' => 'About Fujiyama Technology Solutions', 'body' => 'Fujiyama Technology Solutions (FTS) is a Japanese-rooted IT company in Indonesia that helps businesses turn websites, AI automation, and offshore development into measurable inquiries. FTS combines Japanese quality standards with Indonesian market insight: SEO-friendly website structure, WhatsApp integration, affordable monthly plans, business automation, and digital marketing support.'],
                    'ja' => ['title' => 'Fujiyama Technology Solutionsについて', 'body' => 'Fujiyama Technology Solutions（FTS）は、インドネシアの日系IT企業です。Webサイト、AI自動化、オフショア開発を、測定できる問い合わせへとつなげます。日本の品質基準とインドネシア市場への理解を組み合わせ、SEOに強いサイト構造、WhatsApp連携、手頃な月額プラン、業務自動化、デジタルマーケティング支援を提供します。'],
                ],
                'tags' => ['about', 'fts', 'company', 'overview'],
            ],
            [
                'category' => HotelKnowledgeItem::CATEGORY_GENERAL,
                'title' => 'Yoshihiro Nakagawa, Founder / President Director',
                'body' => 'Sejak memulai bisnis IT di Mongolia pada 2009, Yoshihiro Nakagawa terlibat dalam bisnis IT dan digital secara internasional. Sebagai pendiri FTS, ia menjunjung prinsip kualitas dan tanggung jawab ala Jepang, dan ingin menghadirkan layanan Web, AI, SaaS, dan digital lainnya yang benar-benar sesuai kebutuhan pelanggan bersama para profesional di Indonesia. Dengan menghubungkan Jepang dan Indonesia, ia ingin FTS menjadi mitra teknologi jangka panjang yang tumbuh bersama pelanggannya.',
                'translations' => [
                    'en' => ['title' => 'Yoshihiro Nakagawa, Founder / President Director', 'body' => 'Since starting his IT business in Mongolia in 2009, Yoshihiro Nakagawa has been involved in IT and digital businesses internationally. As the founder of FTS, he values the Japanese principles of quality and responsibility, and aims to provide Web, AI, SaaS, and other digital services that truly meet customers needs, working together with talented professionals in Indonesia. By connecting Japan and Indonesia, he wants FTS to grow into a long-term technology partner that grows together with its customers.'],
                    'ja' => ['title' => '中川 良裕（Yoshihiro Nakagawa）、創業者／代表取締役', 'body' => '2009年にモンゴルでIT事業を始めて以来、国際的にIT・デジタル事業に携わってきました。FTSの創業者として、日本の品質と責任という考え方を大切にし、インドネシアの優秀な人材とともに、お客様のニーズに本当に応えるWeb、AI、SaaSなどのデジタルサービスを提供することを目指しています。日本とインドネシアをつなぎ、お客様とともに成長する長期的な技術パートナーとしてFTSを育てていきます。'],
                ],
                'tags' => ['team', 'founder', 'president director', 'yoshihiro nakagawa'],
            ],
            [
                'category' => HotelKnowledgeItem::CATEGORY_GENERAL,
                'title' => 'Takakazu Kaburaki, Chief Operating Officer',
                'body' => 'Takakazu Kaburaki memimpin operasional harian FTS dengan fokus pada keunggulan dan efisiensi. Ia berpengalaman membangun tim berkinerja tinggi dan berperan penting memastikan setiap proyek berjalan sukses serta pelanggan puas. Berbasis di Jepang, ia bekerja erat dengan tim FTS di Indonesia sebagai jembatan antara kedua negara dan mendukung komunikasi serta eksekusi proyek yang lancar.',
                'translations' => [
                    'en' => ['title' => 'Takakazu Kaburaki, Chief Operating Officer', 'body' => 'Takakazu Kaburaki oversees the daily operations of FTS with a strong focus on excellence and efficiency. He is highly experienced in building high-performing teams and plays a key role in ensuring the successful delivery of each project and customer satisfaction. Based in Japan, he works closely with the FTS team in Indonesia, serving as a bridge between the two countries and supporting smooth communication and project execution.'],
                    'ja' => ['title' => '鏑木 孝和（Takakazu Kaburaki）、最高執行責任者', 'body' => '卓越性と効率を重視してFTSの日々の運営を統括しています。高い成果を出すチームづくりの経験が豊富で、各プロジェクトの成功とお客様の満足を支える重要な役割を担います。日本を拠点に、インドネシアのFTSチームと密に連携し、両国の橋渡し役として円滑なコミュニケーションとプロジェクト遂行を支えています。'],
                ],
                'tags' => ['team', 'coo', 'operations', 'takakazu kaburaki'],
            ],
            [
                'category' => HotelKnowledgeItem::CATEGORY_GENERAL,
                'title' => 'Naoki Yoshida, Director',
                'body' => 'Sebagai Director, Naoki Yoshida mengawasi strategi teknologi dan inovasi FTS. Ia jeli membaca tren baru dan bersemangat mengembangkan solusi mutakhir yang menyelesaikan masalah nyata klien.',
                'translations' => [
                    'en' => ['title' => 'Naoki Yoshida, Director', 'body' => 'As a Director, Naoki Yoshida oversees the technology and innovation strategy of FTS. He has a keen eye for emerging trends and is passionate about developing cutting-edge solutions that solve real-world problems for clients.'],
                    'ja' => ['title' => '吉田 直樹（Naoki Yoshida）、取締役', 'body' => '取締役として、FTSの技術・イノベーション戦略を統括しています。新しいトレンドを見抜く力があり、クライアントの現実の課題を解決する最先端のソリューション開発に情熱を注いでいます。'],
                ],
                'tags' => ['team', 'director', 'technology', 'naoki yoshida'],
            ],
            [
                'category' => HotelKnowledgeItem::CATEGORY_GENERAL,
                'title' => 'Takaki Morita, Director',
                'body' => 'Takaki Morita mendukung FTS sejak berdiri dan terus terlibat dalam pertumbuhan perusahaan serta pengembangan teknologinya. Ia terutama bertanggung jawab di bidang AI, mendukung FTS dari sisi teknis dengan mengeksplorasi pemanfaatan teknologi AI secara praktis dan mengintegrasikannya ke layanan baru. Seiring perkembangan AI, ia terus menghadirkan solusi yang bernilai nyata bagi bisnis pelanggan dan memperkuat kemampuan teknologi FTS.',
                'translations' => [
                    'en' => ['title' => 'Takaki Morita, Director', 'body' => 'As one of the members who has supported FTS since its founding, Takaki Morita has remained actively involved in the growth and technology development of the company. He is primarily responsible for the AI field, supporting FTS from a technical perspective by exploring the practical use of AI technologies and their integration into new services. As AI continues to evolve, he works to develop solutions that provide real value to customers businesses and strengthen the technological capabilities of FTS.'],
                    'ja' => ['title' => '森田 貴樹（Takaki Morita）、取締役', 'body' => '創業時からFTSを支えるメンバーの一人として、会社の成長と技術開発に深く関わってきました。主にAI分野を担当し、AI技術の実用的な活用と新サービスへの組み込みを技術面から支えています。AIの進化に合わせて、お客様の事業に実際の価値をもたらすソリューションを開発し、FTSの技術力を高めていきます。'],
                ],
                'tags' => ['team', 'director', 'ai', 'takaki morita'],
            ],
            [
                'category' => HotelKnowledgeItem::CATEGORY_GENERAL,
                'title' => 'Nilai-nilai FTS',
                'body' => 'Empat nilai yang memandu setiap keputusan dan proyek FTS. Inovasi: merangkul teknologi dan solusi kreatif terbaru untuk masalah yang kompleks. Kualitas: setiap proyek dikerjakan dengan teliti dan standar tertinggi. Transparansi: komunikasi terbuka dan kemitraan yang jujur. Pertumbuhan: terus belajar dan membantu klien tumbuh secara berkelanjutan.',
                'translations' => [
                    'en' => ['title' => 'FTS core values', 'body' => 'Four values guide every decision and project at FTS. Innovation: embracing cutting-edge technologies and creative solutions for complex challenges. Quality: every project is delivered with meticulous attention to detail and the highest standards. Transparency: open communication and honest partnerships. Growth: continuous learning and helping clients achieve sustainable growth.'],
                    'ja' => ['title' => 'FTSの価値観', 'body' => 'すべての判断とプロジェクトを導く4つの価値観です。革新：最先端の技術と創造的な解決策で複雑な課題に取り組みます。品質：細部まで丁寧に、最高水準でプロジェクトを仕上げます。透明性：オープンなコミュニケーションと誠実なパートナーシップ。成長：学び続け、お客様の持続的な成長を支えます。'],
                ],
                'tags' => ['values', 'innovation', 'quality', 'transparency', 'growth'],
            ],
        ];
    }

    /**
     * The sixteen live demos listed on ai-solutions.fts-tech.co.id, each a
     * sample business run by an AI assistant.
     *
     * @return array<int, array<string, mixed>>
     */
    private function aiDemoItems(): array
    {
        $rows = [
            'Cafe AI|https://cafe-ai.fts-tech.co.id/cafe-ai|Kafe|Cafe|カフェ|Barista AI|AI Barista|AIバリスタ|Menu;Tempat duduk;Reservasi meja|Menu;Seating;Reserve a table|メニュー;座席;テーブル予約',
            'FTS Restaurant AI|https://restaurant-ai.fts-tech.co.id/|Restoran|Restaurant|レストラン|Host AI|AI Host|AIホスト|Menu;Pesan meja;Pesanan|Menu;Book a table;Order|メニュー;テーブル予約;注文',
            'FTS Hotel AI|https://hotel-ai.fts-tech.co.id/fts-hotel-ai|Hotel|Hotel|ホテル|Concierge AI|AI Concierge|AIコンシェルジュ|Kamar & suite;Fasilitas;Reservasi|Rooms & suites;Facilities;Reservations|客室;施設;予約',
            'FTS Apartment AI|https://apartment-ai.fts-tech.co.id/fts-apartment-ai|Apartemen|Apartment|アパートメント|Concierge AI|AI Concierge|AIコンシェルジュ|Unit;Fasilitas;Pengajuan|Units;Facilities;Apply|物件;施設;入居申込',
            'FTS Car Rental AI|https://car-rental-ai.fts-tech.co.id/fts-car-rental-ai|Rental mobil|Car rental|レンタカー|Agen rental AI|AI Rental Agent|AIレンタルエージェント|Armada;Layanan rental;Reservasi|Fleet;Rental services;Reservation|車両;レンタルサービス;予約',
            'Spa AI|https://spa-ai.fts-tech.co.id/spa-ai|Spa|Spa|スパ|Concierge spa AI|AI Spa Concierge|AIスパコンシェルジュ|Perawatan;Ruangan;Reservasi|Treatments;Rooms;Reservation|トリートメント;個室;予約',
            'Beauty Salon AI|https://beauty-salon-ai.fts-tech.co.id/beauty-salon-ai|Salon kecantikan|Beauty salon|美容サロン|Beautician AI|AI Beautician|AIビューティシャン|Layanan;Stylist;Janji temu|Services;Stylists;Appointment|メニュー;スタイリスト;予約',
            'Fitness Gym AI|https://fitness-gym-ai.fts-tech.co.id/fitness-gym-ai|Pusat kebugaran|Fitness gym|フィットネスジム|Concierge kebugaran AI|AI Fitness Concierge|AIフィットネスコンシェルジュ|Program;Latihan;Reservasi|Programs;Training;Reservation|プログラム;トレーニング;予約',
            'Aurelia Event Hall|https://event-venue-ai.fts-tech.co.id/aurelia-event-hall|Gedung acara|Event hall|イベントホール|Perencana acara AI|AI Event Planner|AIイベントプランナー|Ruang;Fasilitas;Reservasi|Spaces;Facilities;Reservation|スペース;施設;予約',
            'FTS Wedding Venue AI|https://wedding-venue-ai.fts-tech.co.id/fts-wedding-venue-ai|Gedung pernikahan|Wedding venue|結婚式場|Concierge pernikahan AI|AI Wedding Concierge|AIウェディングコンシェルジュ|Hall;Layanan;Reservasi|Halls;Services;Reservation|会場;サービス;予約',
            'FTS Training Venue AI|https://training-venue-ai.fts-tech.co.id/fts-training-venue-ai|Tempat pelatihan|Training venue|研修会場|Koordinator AI|AI Coordinator|AIコーディネーター|Ruangan;Fasilitas;Reservasi|Rooms;Facilities;Reservation|研修室;施設;予約',
            'FTS Recruitment Venue|https://recruitment-venue-ai.fts-tech.co.id/fts-recruitment-venue|Venue rekrutmen|Recruitment venue|採用イベント会場|Koordinator venue AI|AI Venue Coordinator|AI会場コーディネーター|Ruang;Fasilitas;Pesan acara|Spaces;Facilities;Book your event|スペース;施設;イベント予約',
            'Digital Agency AI|https://digital-agency-ai.fts-tech.co.id/digital-agency-ai|Agensi digital|Digital agency|デジタルエージェンシー|Strategis digital AI|AI Digital Strategist|AIデジタルストラテジスト|Katalog;Tim;Konsultasi|Catalog;Team;Consultation|カタログ;チーム;相談',
            'IT Company AI|https://it-company-ai.fts-tech.co.id/it-company-ai|Perusahaan IT|IT company|IT企業|Konsultan solusi AI|AI Solution Consultant|AIソリューションコンサルタント|Layanan;Konsultan;Konsultasi|Services;Consultants;Consultation|サービス;コンサルタント;相談',
            'Software SaaS AI|https://software-saas-ai.fts-tech.co.id/software-saas-ai|Software (SaaS)|Software (SaaS)|SaaSソフトウェア|Konsultan SaaS AI|AI SaaS Consultant|AI SaaSコンサルタント|Produk & paket;Konsultan;Demo produk|Products & plans;Consultants;Product demo|製品・プラン;コンサルタント;デモ申込',
            'Maju Jaya Studio|https://sme-business-ai.fts-tech.co.id/maju-jaya-studio|UMKM|Small business|中小企業|Asisten bisnis AI|AI Business Assistant|AIビジネスアシスタント|Katalog;Area layanan;Pesan jadwal|Catalog;Service areas;Book a slot|カタログ;対応エリア;予約',
        ];

        return array_map(function (string $row): array {
            [$name, $url, $industryId, $industryEn, $industryJa, $roleId, $roleEn, $roleJa, $featuresId, $featuresEn, $featuresJa] = explode('|', $row);
            [$f1, $f2, $f3] = explode(';', $featuresId);
            [$e1, $e2, $e3] = explode(';', $featuresEn);
            [$j1, $j2, $j3] = explode(';', $featuresJa);

            return [
                'category' => HotelKnowledgeItem::CATEGORY_AI,
                'title' => $name,
                // The screenshot ai-solutions.fts-tech.co.id shows for this demo, named after its subdomain.
                'image_url' => '/images/demos/'.strtok((string) parse_url($url, PHP_URL_HOST), '.').'.webp',
                'body' => "Demo live untuk industri {$industryId}, dilayani {$roleId}. Coba langsung di browser: {$f1}, {$f2}, dan {$f3}. Tersedia dalam bahasa Indonesia, Inggris, dan Jepang. Data dalam demo hanya contoh.",
                'translations' => [
                    'en' => ['title' => $name, 'role' => $roleEn, 'menu' => [$e1, $e2, $e3], 'body' => "A live demo for the {$industryEn} industry, run by the {$roleEn}. Try it in your browser: {$e1}, {$e2} and {$e3}. Available in Indonesian, English and Japanese. Demos use sample data."],
                    'ja' => ['title' => $name, 'role' => $roleJa, 'menu' => [$j1, $j2, $j3], 'body' => "{$industryJa}向けのライブデモです。{$roleJa}が接客します。ブラウザですぐに試せます：{$j1}、{$j2}、{$j3}。インドネシア語・英語・日本語に対応。デモはサンプルデータです。"],
                ],
                'details' => [
                    'demo_url' => $url,
                    'role' => $roleId,
                    'menu' => array_map(fn (string $feature) => ['name' => $feature, 'description' => '', 'price' => null], [$f1, $f2, $f3]),
                ],
                'tags' => ['ai demo', 'live demo', strtolower($industryEn), strtolower($roleEn), strtolower($name)],
            ];
        }, $rows);
    }

    /**
     * The services listed on fts-tech.co.id, AI services included: the AI
     * Solutions room is kept for the live demos.
     *
     * @return array<int, array<string, mixed>>
     */
    private function serviceItems(): array
    {
        $service = function (string $title, string $enTitle, string $jaTitle, string $id, string $en, string $ja, string $imageId, array $features, array $tags): array {
            return [
                'category' => HotelKnowledgeItem::CATEGORY_FACILITIES,
                'title' => $title,
                'image_url' => sprintf(self::IMG, $imageId, 800),
                'body' => $id,
                'translations' => [
                    'en' => ['title' => $enTitle, 'body' => $en],
                    'ja' => ['title' => $jaTitle, 'body' => $ja],
                ],
                'details' => [
                    'menu' => array_map(fn (string $feature) => ['name' => $feature, 'description' => '', 'price' => null], $features),
                ],
                'tags' => $tags,
            ];
        };

        return [
            $service(
                'Web Development', 'Web Development', 'Web開発',
                'Kami membangun website dan aplikasi web siap SEO untuk bisnis di Indonesia, dari company profile hingga aplikasi full-stack. Setiap situs cepat, mobile-first, dan dioptimalkan untuk menghasilkan inquiry.',
                'We build SEO-ready websites and web applications for Indonesian businesses, from company profiles to full-stack apps. Every site is fast, mobile-first, and optimized for lead conversion.',
                'インドネシア企業向けに、SEOに強いWebサイトとWebアプリを制作します。コーポレートサイトからフルスタックアプリまで、高速でモバイルファースト、問い合わせ獲得に最適化されています。',
                'photo-1461749280684-dccba630e2f6',
                ['SEO-Friendly Website Structure', 'WhatsApp Integration for Leads', 'Responsive Mobile-First Design', 'Performance & Core Web Vitals'],
                ['web development', 'website', 'seo', 'whatsapp'],
            ),
            $service(
                'AI Automation', 'AI Automation', 'AI自動化',
                'Otomatiskan alur kerja berulang, follow-up penjualan, dan dukungan pelanggan dengan AI automation yang disesuaikan untuk bisnis Indonesia. Hemat waktu dan tumbuh tanpa menambah karyawan.',
                'Automate repetitive workflows, sales follow-ups, and customer support with AI automation tailored for Indonesian businesses. Save time and scale without adding headcount.',
                '繰り返しの業務、営業フォロー、カスタマーサポートを、インドネシアの企業に合わせたAI自動化で効率化します。人員を増やさずに事業を拡大できます。',
                'photo-1677442136019-21780ecad995',
                ['Sales & Support Automation', 'Business Workflow Streamlining', 'AI-Powered Reporting', 'WhatsApp & CRM Integration'],
                ['ai automation', 'automation', 'workflow', 'crm'],
            ),
            $service(
                'AI Agent Development', 'AI Agent Development', 'AIエージェント開発',
                'Bangun agen AI cerdas yang menangani pertanyaan pelanggan, kualifikasi lead, dan operasional internal secara mandiri, dirancang sesuai logika dan tujuan bisnis Anda.',
                'Build intelligent AI agents that autonomously handle customer inquiries, lead qualification, and internal operations, designed around your business logic and goals.',
                'お客様の問い合わせ対応、リードの選別、社内業務を自律的にこなすAIエージェントを、貴社の業務ロジックと目標に合わせて開発します。',
                'photo-1620712943543-bcc4688e7485',
                ['Custom AI Agent Design', 'Multi-Channel Deployment', 'API & System Integration', 'Continuous Improvement Setup'],
                ['ai agent', 'agent', 'chatbot', 'lead qualification'],
            ),
            $service(
                'Offshore Development', 'Offshore Development', 'オフショア開発',
                'Tim developer khusus di Indonesia untuk perusahaan Jepang, dengan standar kualitas Jepang, koordinasi dua bahasa, dan manajemen proyek yang transparan.',
                'Dedicated offshore development teams in Indonesia for Japanese companies, with Japanese-standard quality, bilingual coordination, and transparent project management.',
                '日本企業向けに、日本品質、バイリンガルでの調整、透明性のあるプロジェクト管理を備えたインドネシアの専任オフショア開発チームを提供します。',
                'photo-1521737604893-d14cc237f11d',
                ['Dedicated Developer Teams', 'Japanese-Standard QA Process', 'Bilingual Project Coordination', 'Flexible Team Scaling'],
                ['offshore', 'offshore development', 'japanese companies', 'developer team'],
            ),
            $service(
                'Website Restoran', 'Restaurant Website', 'レストランサイト制作',
                'Website restoran siap WhatsApp dengan menu digital, form reservasi, dan SEO lokal, dirancang untuk mengubah pengunjung menjadi pelanggan dan mengisi meja.',
                'WhatsApp-ready restaurant websites with digital menus, reservation forms, and local SEO, designed to convert visitors into customers and fill tables.',
                'デジタルメニュー、予約フォーム、ローカルSEOを備えたWhatsApp対応のレストランサイト。訪問者を顧客に変え、席を埋めます。',
                'photo-1555396273-367ea4eb4db5',
                ['Digital Menu & Photo Gallery', 'WhatsApp Ordering Buttons', 'Local SEO for Google Maps', 'Reservation & Event Pages'],
                ['restaurant', 'restaurant website', 'menu', 'reservation'],
            ),
            $service(
                'Otomasi Bisnis & Digital Marketing', 'Business Automation & Digital Marketing', '業務自動化・デジタルマーケティング',
                'Otomasi bisnis dari pengumpulan lead hingga konversi. FTS membangun sistem yang menghubungkan website, WhatsApp, dan CRM untuk hasil yang terukur.',
                'End-to-end business automation from lead capture to conversion tracking. FTS builds systems that connect your website, WhatsApp, and CRM to generate measurable results.',
                'リード獲得からコンバージョン計測まで、業務を一気通貫で自動化します。Webサイト、WhatsApp、CRMをつなぎ、測定できる成果を生み出します。',
                'photo-1551288049-bebda4e38f71',
                ['Lead Capture & Nurturing', 'Analytics & Conversion Tracking', 'WhatsApp Campaign Automation', 'Digital Marketing Support'],
                ['business automation', 'digital marketing', 'lead capture', 'analytics'],
            ),
        ];
    }

    /**
     * The twelve projects listed on fts-tech.co.id/projects/all, with the images
     * that site serves.
     *
     * @return array<int, array<string, mixed>>
     */
    private function portfolioItems(): array
    {
        $project = fn (string $title, string $image, string $id, string $en, string $ja, array $tags): array => [
            'category' => HotelKnowledgeItem::CATEGORY_PORTFOLIO,
            'title' => $title,
            'image_url' => 'https://fts-tech.co.id/images/portfolio/'.$image.'.jpg',
            'body' => $id,
            'translations' => [
                'en' => ['title' => $title, 'body' => $en],
                'ja' => ['title' => $title, 'body' => $ja],
            ],
            'tags' => ['portfolio', 'project', ...$tags],
        ];

        return [
            $project('EDEPA World', 'edepa',
                'Website company profile untuk EDEPA World, agensi kreatif Jepang, yang menampilkan ringkasan bisnis, portofolio proyek, dan topik terbaru.',
                'Company profile website for EDEPA World, a Japanese creative agency, presenting their business overview, project portfolio, and latest topics.',
                '日本のクリエイティブエージェンシーEDEPA WORLDのコーポレートサイト。事業概要、制作実績、最新トピックを紹介しています。',
                ['creative agency', 'company profile', 'portfolio', 'japan']),
            $project('FBE - Fujiyama Biomass Energy', 'fbe',
                'Website korporat Fujiyama Biomass Energy yang mempromosikan produk arang PKS premium, inisiatif keberlanjutan, dan kemitraan ekspor.',
                'Corporate website for Fujiyama Biomass Energy, promoting premium PKS charcoal products, sustainability initiatives, and export partnerships.',
                'Fujiyama Biomass Energyのコーポレートサイト。高品質なPKS炭製品、サステナビリティへの取り組み、輸出パートナーシップを紹介しています。',
                ['biomass energy', 'sustainability', 'export', 'indonesia']),
            $project('Building Maintenance', 'building-maintenance',
                'Website company profile untuk perusahaan perawatan gedung di Jepang yang menonjolkan keahlian pengelolaan fasilitas dan layanannya.',
                'Company profile website for a Japanese building maintenance firm, highlighting specialized facility management expertise and service offerings.',
                '日本のビルメンテナンス会社のコーポレートサイト。専門的な施設管理のノウハウとサービス内容を紹介しています。',
                ['building maintenance', 'facility management', 'company profile', 'japan']),
            $project('LookLook', 'looklook',
                'Platform informasi wisata untuk pengunjung Jepang, dengan pencarian restoran, tempat wisata, info WiFi gratis, dan pencarian multibahasa per wilayah.',
                'Travel information platform for visitors to Japan, offering restaurant search, sightseeing spots, free WiFi info, and multilingual search by region.',
                '訪日観光客向けの旅行情報プラットフォーム。レストラン検索、観光スポット、無料WiFi情報、地域別の多言語検索を提供しています。',
                ['travel platform', 'tourism', 'directory', 'japan']),
            $project('Minnano Kuruma', 'minnano-kuruma',
                'Landing page lokal untuk layanan pembelian mobil bekas di kawasan Nishitama, Jepang, dengan panggilan penilaian gratis, dukungan LINE, dan bukti kepercayaan pelanggan.',
                'Local landing page for a used car buying service in the Nishitama area of Japan, featuring free appraisal calls, LINE support, and customer trust signals.',
                '西多摩エリアの中古車買取サービスのローカルLP。無料査定の電話、LINE対応、お客様の信頼を伝える要素を備えています。',
                ['used car buying', 'automotive', 'local business', 'japan']),
            $project('XchangePro', 'xchangepro',
                'Website bisnis untuk toko ban dan velg yang menampilkan penjualan ban, jasa pemasangan dan penggantian, serta perawatan mobil di kawasan Tokyo/Saitama.',
                'Business website for a tire and wheel shop, showcasing tire sales, installation and exchange services, and car maintenance in the Tokyo/Saitama area.',
                'タイヤ・ホイール専門店のサイト。東京・埼玉エリアでのタイヤ販売、取付・交換サービス、車のメンテナンスを紹介しています。',
                ['tire shop', 'automotive', 'service business', 'japan']),
            $project('Ebila Hall', 'ebilahall',
                'Website pemesanan untuk aula musik dan acara serbaguna di Shinagawa, dengan reservasi online untuk pernikahan, konser, dan acara pribadi.',
                'Booking website for a versatile music and event hall in Shinagawa Ward, featuring online reservations for weddings, concerts, and private gatherings.',
                '品川区の多目的ミュージック・イベントホールの予約サイト。結婚式、コンサート、プライベートな集まりのオンライン予約に対応しています。',
                ['event venue', 'booking system', 'hospitality', 'japan']),
            $project('Hiking (San Kou Kai)', 'hiking',
                'Website komunitas untuk perkumpulan hiking di Jepang, berisi laporan perjalanan, rencana pendakian, dan informasi keanggotaan untuk pendakian yang aman.',
                'Community website for a Japanese hiking association, sharing trip reports, hiking plans, and membership information for safe mountain climbing.',
                '日本の登山会のコミュニティサイト。山行報告、登山計画、会員情報を共有し、安全な山登りを支えています。',
                ['community website', 'hiking club', 'blog', 'japan']),
            $project('Car Repair', 'car-repair',
                'Website bisnis untuk bengkel perbaikan bodi mobil yang menampilkan layanan perbaikan, sistem pemesanan online, dan testimoni pelanggan.',
                'Business website for an auto body repair shop, showcasing repair services, an online booking system, and customer testimonials.',
                '自動車板金工場のサイト。修理サービス、オンライン予約システム、お客様の声を紹介しています。',
                ['auto repair', 'booking system', 'automotive', 'service business']),
            $project('Thai Travel', 'thai-travel',
                'Platform pemesanan perjalanan untuk Thailand, dengan antar-jemput bandara, sewa mobil pribadi, dan paket tur lewat sistem pencarian dan pemesanan online.',
                'Travel booking platform for Thailand, offering airport transfers, private car rentals, and tour packages through an online search and booking system.',
                'タイ旅行の予約プラットフォーム。空港送迎、貸切車、ツアーパッケージをオンラインで検索・予約できます。',
                ['travel booking', 'tourism', 'transportation', 'thailand']),
            $project('FTS Menu', 'fts-menu',
                'Landing page SaaS untuk platform menu digital berbasis QR code yang memungkinkan restoran dan kafe memperbarui harga, foto, dan ketersediaan secara real time.',
                'SaaS landing page for a digital QR-code menu platform that lets restaurants and cafes update prices, photos, and availability in real time.',
                'QRコード式デジタルメニューのSaaSランディングページ。レストランやカフェが価格、写真、在庫状況をリアルタイムで更新できます。',
                ['saas', 'digital menu', 'restaurant tech', 'qr code']),
            $project('ens. Hair Salon', 'ens',
                'Website bisnis untuk ens., salon rambut di Hachioji, Jepang, yang menampilkan konsep, menu dan harga, staf, galeri, dan kontak online.',
                'Business website for ens., a hair salon in Hachioji, Japan, featuring their concept, menu and pricing, staff, gallery, and online contact.',
                '八王子のヘアサロンens.のサイト。コンセプト、メニューと料金、スタッフ、ギャラリー、オンライン問い合わせを紹介しています。',
                ['hair salon', 'beauty', 'company profile', 'japan']),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function officeItems(): array
    {
        return [
            [
                'category' => HotelKnowledgeItem::CATEGORY_TRANSPORT,
                'title' => 'Kunjungi kantor FTS di Jakarta Barat',
                'image_url' => sprintf(self::IMG, 'photo-1497366216548-37526070297c', 800),
                'body' => 'Kantor pusat FTS berada di Neo Soho Mall, Jalan Let. Jend. S. Parman Kav. 28 Unit 2011, Tanjung Duren Selatan, Grogol Petamburan, Jakarta Barat, DKI Jakarta 11470. Kantor buka Senin sampai Jumat pukul 09.00 sampai 18.00. Telepon +62 895 2933 6179, email info@fts-tech.co.id.',
                'translations' => [
                    'en' => ['title' => 'Visit the FTS office in West Jakarta', 'body' => 'The FTS headquarters is at Neo Soho Mall, Jalan Let. Jend. S. Parman Kav. 28 Unit 2011, Tanjung Duren Selatan, Grogol Petamburan, West Jakarta, DKI Jakarta 11470. The office is open Monday to Friday, 9 AM to 6 PM. Phone +62 895 2933 6179, email info@fts-tech.co.id.'],
                    'ja' => ['title' => 'FTSの西ジャカルタオフィスへ', 'body' => 'FTSの本社は、Neo Soho Mall（Jalan Let. Jend. S. Parman Kav. 28 Unit 2011, Tanjung Duren Selatan, Grogol Petamburan, 西ジャカルタ, DKI Jakarta 11470）にあります。営業時間は月曜から金曜の9:00〜18:00です。電話は +62 895 2933 6179、メールは info@fts-tech.co.id です。'],
                ],
                'details' => ['hours' => '09:00–18:00', 'menu' => []],
                'tags' => ['office', 'location', 'address', 'neo soho', 'jakarta'],
            ],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function policyItems(): array
    {
        return [
            [
                'category' => HotelKnowledgeItem::CATEGORY_POLICIES,
                'title' => 'Jam kerja dan waktu respons',
                'body' => 'Kantor FTS buka Senin sampai Jumat pukul 09.00 sampai 18.00. Email dibalas dalam 4 jam, telepon langsung dijawab, dan penawaran proyek dikirim dalam 24 jam.',
                'translations' => [
                    'en' => ['title' => 'Business hours and response time', 'body' => 'The FTS office is open Monday to Friday, 9 AM to 6 PM. Emails are answered within 4 hours, phone calls are answered immediately, and project quotes are sent within 24 hours.'],
                    'ja' => ['title' => '営業時間と対応時間', 'body' => 'FTSのオフィスは月曜から金曜の9:00〜18:00に営業しています。メールは4時間以内、電話は即時に対応し、プロジェクトのお見積りは24時間以内にお送りします。'],
                ],
                'tags' => ['business hours', 'response time', 'contact'],
            ],
            [
                'category' => HotelKnowledgeItem::CATEGORY_POLICIES,
                'title' => 'Kontrak dan biaya paket website',
                'body' => 'Desain dan pengembangan website gratis. Anda hanya membayar biaya server bulanan sesuai paket: Rp 700.000 per bulan untuk hingga 5 halaman, Rp 1.200.000 untuk hingga 10 halaman, dan Rp 1.600.000 untuk hingga 15 halaman. Kontrak berlaku 36 bulan dan 6 bulan terakhir gratis. Tidak ada biaya tersembunyi.',
                'translations' => [
                    'en' => ['title' => 'Contract and website plan fees', 'body' => 'Website design and development are free. You only pay a monthly server fee based on the plan: Rp 700,000 per month for up to 5 pages, Rp 1,200,000 for up to 10 pages, and Rp 1,600,000 for up to 15 pages. The contract runs for 36 months and the last 6 months are free. There are no hidden fees.'],
                    'ja' => ['title' => '契約とWebサイトプランの料金', 'body' => 'Webサイトの設計・開発は無料です。お支払いはプランごとの月額サーバー費用のみで、5ページまでRp 700,000、10ページまでRp 1,200,000、15ページまでRp 1,600,000です。契約期間は36か月で、最後の6か月は無料です。隠れた費用はありません。'],
                ],
                'tags' => ['contract', 'pricing', 'server fee', '36 months', 'free design'],
            ],
            [
                'category' => HotelKnowledgeItem::CATEGORY_POLICIES,
                'title' => 'Proyek lebih dari 15 halaman',
                'body' => 'Untuk website lebih dari 15 halaman, FTS memberikan penawaran khusus. Lingkup, fitur, dan timeline disesuaikan dengan kebutuhan Anda. Permintaan penawaran dijawab dalam 24 jam.',
                'translations' => [
                    'en' => ['title' => 'Projects over 15 pages', 'body' => 'For websites of more than 15 pages, FTS provides a custom quotation. Scope, features, and timeline are tailored to your needs. Quote requests are answered within 24 hours.'],
                    'ja' => ['title' => '15ページを超えるプロジェクト', 'body' => '15ページを超えるWebサイトは、個別にお見積りします。範囲、機能、納期はご要望に合わせて調整します。お見積りのご依頼には24時間以内にお答えします。'],
                ],
                'tags' => ['quotation', 'custom project', 'more than 15 pages'],
            ],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function faqItems(): array
    {
        return [
            [
                'category' => HotelKnowledgeItem::CATEGORY_FAQ,
                'title' => 'Berapa biaya membuat website?',
                'body' => 'Ada tiga paket bulanan: hingga 5 halaman Rp 700.000, hingga 10 halaman Rp 1.200.000, dan hingga 15 halaman Rp 1.600.000 per bulan. Lebih dari 15 halaman dihitung lewat penawaran khusus.',
                'translations' => [
                    'en' => ['title' => 'How much does a website cost?', 'body' => 'There are three monthly plans: up to 5 pages at Rp 700,000, up to 10 pages at Rp 1,200,000, and up to 15 pages at Rp 1,600,000 per month. More than 15 pages is priced through a custom quotation.'],
                    'ja' => ['title' => 'Webサイトの費用はいくらですか？', 'body' => '月額プランは3種類で、5ページまでRp 700,000、10ページまでRp 1,200,000、15ページまでRp 1,600,000です。15ページを超える場合は個別にお見積りします。'],
                ],
                'tags' => ['price', 'cost', 'faq'],
            ],
            [
                'category' => HotelKnowledgeItem::CATEGORY_FAQ,
                'title' => 'Apakah ada biaya desain dan pengembangan?',
                'body' => 'Tidak. Desain dan pengembangan website gratis. Anda hanya membayar biaya server bulanan sesuai paket, dengan kontrak 36 bulan dan 6 bulan terakhir gratis.',
                'translations' => [
                    'en' => ['title' => 'Is there a design and development fee?', 'body' => 'No. Website design and development are free. You only pay the monthly server fee for your plan, on a 36-month contract with the last 6 months free.'],
                    'ja' => ['title' => '設計・開発の費用はかかりますか？', 'body' => 'いいえ、Webサイトの設計・開発は無料です。お支払いはプランごとの月額サーバー費用のみで、契約は36か月、最後の6か月は無料です。'],
                ],
                'tags' => ['free', 'design', 'development', 'faq'],
            ],
            [
                'category' => HotelKnowledgeItem::CATEGORY_FAQ,
                'title' => 'Apakah website bisa terhubung dengan WhatsApp?',
                'body' => 'Ya. FTS menyediakan integrasi WhatsApp agar inquiry pelanggan masuk lebih cepat. Untuk website restoran, tersedia juga tombol pesan lewat WhatsApp.',
                'translations' => [
                    'en' => ['title' => 'Can the website connect to WhatsApp?', 'body' => 'Yes. FTS provides WhatsApp integration so customer inquiries reach you faster. Restaurant websites also include WhatsApp ordering buttons.'],
                    'ja' => ['title' => 'WhatsAppと連携できますか？', 'body' => 'はい。FTSはWhatsApp連携を提供しており、お客様の問い合わせがより早く届きます。レストランサイトにはWhatsAppで注文できるボタンも用意しています。'],
                ],
                'tags' => ['whatsapp', 'integration', 'faq'],
            ],
            [
                'category' => HotelKnowledgeItem::CATEGORY_FAQ,
                'title' => 'Apakah FTS menyediakan tim offshore untuk perusahaan Jepang?',
                'body' => 'Ya. FTS menyediakan tim developer khusus di Indonesia untuk perusahaan Jepang, dengan proses QA standar Jepang, koordinasi proyek dua bahasa, dan skala tim yang fleksibel.',
                'translations' => [
                    'en' => ['title' => 'Does FTS provide offshore teams for Japanese companies?', 'body' => 'Yes. FTS provides dedicated developer teams in Indonesia for Japanese companies, with a Japanese-standard QA process, bilingual project coordination, and flexible team scaling.'],
                    'ja' => ['title' => '日本企業向けのオフショアチームはありますか？', 'body' => 'はい。FTSは日本企業向けに、日本基準のQAプロセス、バイリンガルのプロジェクト調整、柔軟なチーム拡張を備えたインドネシアの専任開発チームを提供しています。'],
                ],
                'tags' => ['offshore', 'japan', 'faq'],
            ],
            [
                'category' => HotelKnowledgeItem::CATEGORY_FAQ,
                'title' => 'Bagaimana cara memulai proyek dengan FTS?',
                'body' => 'Hubungi FTS lewat WhatsApp di +62 895 2933 6179, email info@fts-tech.co.id, atau telepon langsung. Email dibalas dalam 4 jam dan penawaran proyek dikirim dalam 24 jam. Anda juga bisa datang ke kantor di Neo Soho Mall, Jakarta Barat.',
                'translations' => [
                    'en' => ['title' => 'How do I start a project with FTS?', 'body' => 'Contact FTS through WhatsApp at +62 895 2933 6179, email at info@fts-tech.co.id, or call directly. Emails are answered within 4 hours and project quotes are sent within 24 hours. You can also visit the office at Neo Soho Mall, West Jakarta.'],
                    'ja' => ['title' => 'FTSでプロジェクトを始めるには？', 'body' => 'WhatsApp（+62 895 2933 6179）、メール（info@fts-tech.co.id）、またはお電話でご連絡ください。メールは4時間以内、お見積りは24時間以内にお届けします。西ジャカルタのNeo Soho Mallにあるオフィスにお越しいただくこともできます。'],
                ],
                'tags' => ['start project', 'contact', 'faq'],
            ],
        ];
    }
}
