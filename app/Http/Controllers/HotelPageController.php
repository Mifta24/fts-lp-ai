<?php

namespace App\Http\Controllers;

use App\Core\Member\Support\MemberLabels;
use App\Models\Hotel;
use App\Models\HotelKnowledgeItem;
use App\Models\RoomType;
use App\Services\Reservation\MemberBookings;
use App\Services\Reservation\ReservationHandover;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HotelPageController extends Controller
{
    private const SUPPORTED_LOCALES = ['id', 'en', 'ja'];

    /**
     * Rooms that list knowledge items. They share one list view and one detail
     * view; each takes the items of its own categories.
     *
     * @var array<string, array{route: string, categories: list<string>}>
     */
    private const SECTIONS = [
        'facilities' => ['route' => 'hotel.facilities', 'categories' => ['facilities', 'dining', 'transport']],
        'solutions' => ['route' => 'hotel.solutions', 'categories' => ['ai']],
        'portfolio' => ['route' => 'hotel.portfolio', 'categories' => ['portfolio']],
    ];

    /** The default cut-out of the concierge, layered in front of a scene's background when a scene does not name its own. */
    private const CHARACTER_IMAGE = 'images/character.png';

    public function __construct(
        private readonly ReservationHandover $handover,
        private readonly MemberBookings $memberBookings,
    ) {}

    /**
     * What the AI concierge says when the guest steps into the rooms scenes.
     * Every sentence is built from stored hotel data, never generated.
     *
     * @var array<string, array<string, string>>
     */
    private const NARRATION = [
        'en' => [
            'rooms' => 'Here are our website plans. We have :count plans, from :price per month. Open any of them and I will walk you through it, or ask me anything.',
            'rooms_one' => 'Here is our website plan. We have one plan, from :price per month. Open it and I will walk you through it, or ask me anything.',
            'size_guests' => 'It offers :size m² for up to :guests users.',
            'guests' => 'It supports up to :guests users.',
            'breakfast' => 'Breakfast is included.',
            'price' => 'The fee starts from :price per month. Final scope and rates are confirmed by our team.',
            'facilities' => 'We offer :count services, including :examples. Choose one to read the details, or ask me anything.',
            'facilities_one' => 'We offer :examples. Open it to read the details, or ask me anything.',
            'info_welcome' => 'Welcome to :hotel.',
            'info_place' => 'We are located in :location.',
            'info_hours' => 'Our office is open from :in to :out, Monday to Friday.',
            'info_more' => 'Below you will find the address, our terms and frequently asked questions — or just ask me.',
            'tour_rooms' => 'This way — let me show you our website plans.',
            'tour_facilities' => 'Come, let me show you our services.',
            'tour_info' => 'Let me tell you more about FTS.',
            'tour_staff' => 'Let me bring you to our team.',
            'solutions' => 'Here are our AI solutions. We offer :count, including :examples. Choose one to read the details, or ask me anything.',
            'solutions_one' => 'We offer :examples. Open it to read the details, or ask me anything.',
            'portfolio' => 'Here is our portfolio. We have :count projects, including :examples. Choose one to read about it, or ask me anything.',
            'portfolio_one' => 'Here is one of our projects: :examples. Open it to read about it, or ask me anything.',
            'tour_solutions' => 'This way — let me show you our AI solutions.',
            'tour_portfolio' => 'Come, let me show you our recent projects.',
            'tour_reservation' => 'Let me help you plan your project.',
            'tour_lobby' => 'Let me walk you back to the lobby.',
            'tour_room' => 'Let me show you :name.',
            'listen' => 'Listen', 'stop' => 'Stop', 'skip' => 'Skip', 'speaks' => 'is speaking',
        ],
        'id' => [
            'rooms' => 'Inilah paket website kami. Ada :count paket, mulai dari :price per bulan. Buka salah satu, nanti saya jelaskan — atau tanyakan apa saja kepada saya.',
            'rooms_one' => 'Inilah paket website kami. Ada satu paket, mulai dari :price per bulan. Buka paketnya, nanti saya jelaskan — atau tanyakan apa saja kepada saya.',
            'size_guests' => 'Ukuran :size m², untuk maksimal :guests pengguna.',
            'guests' => 'Paket ini untuk maksimal :guests pengguna.',
            'breakfast' => 'Sudah termasuk sarapan.',
            'price' => 'Biaya mulai dari :price per bulan. Lingkup dan biaya final dikonfirmasi oleh tim kami.',
            'facilities' => 'Kami memiliki :count layanan, di antaranya :examples. Pilih salah satu untuk membaca detailnya, atau tanyakan apa saja kepada saya.',
            'facilities_one' => 'Kami memiliki :examples. Buka untuk membaca detailnya, atau tanyakan apa saja kepada saya.',
            'info_welcome' => 'Selamat datang di :hotel.',
            'info_place' => 'Kami berada di :location.',
            'info_hours' => 'Kantor kami buka pukul :in sampai :out, Senin sampai Jumat.',
            'info_more' => 'Di bawah ini ada alamat, ketentuan, dan pertanyaan yang sering diajukan — atau tanyakan langsung kepada saya.',
            'tour_rooms' => 'Mari, saya antar ke paket website kami.',
            'tour_facilities' => 'Mari, saya antar melihat layanan kami.',
            'tour_info' => 'Mari, saya ceritakan lebih lanjut tentang FTS.',
            'tour_staff' => 'Mari, saya antar Anda bertemu tim kami.',
            'solutions' => 'Inilah solusi AI kami. Ada :count solusi, di antaranya :examples. Pilih salah satu untuk membaca detailnya, atau tanyakan apa saja kepada saya.',
            'solutions_one' => 'Kami memiliki :examples. Buka untuk membaca detailnya, atau tanyakan apa saja kepada saya.',
            'portfolio' => 'Inilah portofolio kami. Ada :count proyek, di antaranya :examples. Pilih salah satu untuk membacanya, atau tanyakan apa saja kepada saya.',
            'portfolio_one' => 'Inilah salah satu proyek kami: :examples. Buka untuk membacanya, atau tanyakan apa saja kepada saya.',
            'tour_solutions' => 'Mari, saya antar melihat solusi AI kami.',
            'tour_portfolio' => 'Mari, saya tunjukkan proyek-proyek terbaru kami.',
            'tour_reservation' => 'Mari, saya bantu merencanakan proyek Anda.',
            'tour_lobby' => 'Mari saya antar kembali ke lobi.',
            'tour_room' => 'Mari saya tunjukkan :name.',
            'listen' => 'Dengarkan', 'stop' => 'Berhenti', 'skip' => 'Lewati', 'speaks' => 'sedang berbicara',
        ],
        'ja' => [
            'rooms' => 'こちらがWebサイトのプランです。:count プラン、月額 :price からご用意しています。プランを開いていただければ、ご案内いたします。ご質問もお気軽にどうぞ。',
            'rooms_one' => 'こちらがWebサイトのプランです。1プラン、月額 :price からご用意しています。プランを開いていただければ、ご案内いたします。ご質問もお気軽にどうぞ。',
            'size_guests' => '',
            'guests' => '',
            'breakfast' => '朝食付きです。',
            'price' => '料金は月額 :price からです。範囲と料金は、スタッフが最終確認いたします。',
            'facilities' => ':count件のサービスをご用意しています。:examples などです。選ぶと詳細をご覧いただけます。ご質問もどうぞ。',
            'facilities_one' => ':examples をご用意しています。詳細をご覧ください。ご質問もどうぞ。',
            'info_welcome' => 'ようこそ、:hotel へ。',
            'info_place' => '所在地は:locationです。',
            'info_hours' => '営業時間は月曜から金曜の:in〜:outです。',
            'info_more' => '以下に、所在地、ご利用条件、よくあるご質問をご案内しています。お気軽にお尋ねください。',
            'tour_rooms' => 'こちらへどうぞ。Webサイトのプランへご案内します。',
            'tour_facilities' => 'こちらへどうぞ。サービスへご案内します。',
            'tour_info' => 'FTSについてご案内します。',
            'tour_staff' => 'スタッフのもとへご案内します。',
            'solutions' => 'こちらがAIソリューションです。:count件、:examples などをご用意しています。選ぶと詳細をご覧いただけます。ご質問もどうぞ。',
            'solutions_one' => ':examples をご用意しています。詳細をご覧ください。ご質問もどうぞ。',
            'portfolio' => 'こちらが制作実績です。:count件、:examples などがあります。選ぶと詳細をご覧いただけます。ご質問もどうぞ。',
            'portfolio_one' => ':examples の制作実績です。詳細をご覧ください。ご質問もどうぞ。',
            'tour_solutions' => 'こちらへどうぞ。AIソリューションへご案内します。',
            'tour_portfolio' => 'こちらへどうぞ。最近のプロジェクトをご紹介します。',
            'tour_reservation' => 'プロジェクトのご計画をお手伝いします。',
            'tour_lobby' => 'ロビーへご案内します。',
            'tour_room' => ':name をご案内します。',
            'listen' => '音声で聞く', 'stop' => '停止', 'skip' => 'スキップ', 'speaks' => '話しています',
        ],
    ];

    /**
     * Guest-facing names for the coded room values stored in the database.
     *
     * @var array<string, array{bed: array<string, string>, view: array<string, string>, amenity: array<string, string>}>
     */
    private const ROOM_TERMS = [
        'en' => [
            'bed' => ['king' => 'King bed', 'queen' => 'Queen bed', 'twin' => 'Twin bed', 'single' => 'Single bed', 'double' => 'Double bed'],
            'view' => ['garden' => 'Garden view', 'ocean' => 'Ocean view', 'pool' => 'Pool view', 'city' => 'City view', 'mountain' => 'Mountain view'],
            'amenity' => ['air_conditioning' => 'Air conditioning', 'wifi' => 'Wi-Fi', 'minibar' => 'Minibar', 'safe_deposit_box' => 'Safe deposit box', 'balcony' => 'Balcony', 'bathtub' => 'Bathtub', 'coffee_maker' => 'Coffee maker', 'living_room' => 'Living room', 'private_pool' => 'Private pool'],
        ],
        'id' => [
            'bed' => ['king' => 'Tempat tidur king', 'queen' => 'Tempat tidur queen', 'twin' => 'Tempat tidur twin', 'single' => 'Tempat tidur single', 'double' => 'Tempat tidur double'],
            'view' => ['garden' => 'Pemandangan taman', 'ocean' => 'Pemandangan laut', 'pool' => 'Pemandangan kolam', 'city' => 'Pemandangan kota', 'mountain' => 'Pemandangan gunung'],
            'amenity' => ['air_conditioning' => 'AC', 'wifi' => 'Wi-Fi', 'minibar' => 'Minibar', 'safe_deposit_box' => 'Brankas', 'balcony' => 'Balkon', 'bathtub' => 'Bak mandi', 'coffee_maker' => 'Mesin kopi', 'living_room' => 'Ruang tamu', 'private_pool' => 'Kolam renang pribadi'],
        ],
        'ja' => [
            'bed' => ['king' => 'キングベッド', 'queen' => 'クイーンベッド', 'twin' => 'ツインベッド', 'single' => 'シングルベッド', 'double' => 'ダブルベッド'],
            'view' => ['garden' => 'ガーデンビュー', 'ocean' => 'オーシャンビュー', 'pool' => 'プールビュー', 'city' => 'シティビュー', 'mountain' => 'マウンテンビュー'],
            'amenity' => ['air_conditioning' => 'エアコン', 'wifi' => 'Wi-Fi', 'minibar' => 'ミニバー', 'safe_deposit_box' => 'セーフティボックス', 'balcony' => 'バルコニー', 'bathtub' => 'バスタブ', 'coffee_maker' => 'コーヒーメーカー', 'living_room' => 'リビングルーム', 'private_pool' => 'プライベートプール'],
        ],
    ];

    public function index(Request $request): View
    {
        $hotels = Hotel::where('public_status', 'published')->orderBy('name')->get();

        $locale = in_array($request->query('lang'), self::SUPPORTED_LOCALES, true)
            ? $request->query('lang')
            : ($hotels->first()?->default_locale ?? 'id');

        return view('opening', [
            'hotels' => $hotels,
            'locale' => $locale,
            'supportedLocales' => self::SUPPORTED_LOCALES,
            'opening' => $this->openingLabels($locale),
        ]);
    }

    public function show(Request $request, string $hotelSlug): View
    {
        [$hotel, $locale] = $this->resolveStage($request, $hotelSlug);

        return view('hotel.show', $this->stageData($hotel, $locale, 'lobby'));
    }

    public function rooms(Request $request, string $hotelSlug): View
    {
        [$hotel, $locale] = $this->resolveStage($request, $hotelSlug);

        return view('hotel.rooms-index', $this->stageData($hotel, $locale, 'rooms'));
    }

    public function room(Request $request, string $hotelSlug, string $roomSlug): View
    {
        [$hotel, $locale] = $this->resolveStage($request, $hotelSlug);

        $data = $this->stageData($hotel, $locale, 'room');
        $rooms = $data['roomTypes'];
        $index = $rooms->search(fn (RoomType $roomType) => $roomType->slug === $roomSlug);

        abort_if($index === false, 404);

        return view('hotel.room-detail', [
            ...$data,
            'roomType' => $rooms[$index],
            'roomIndex' => $index,
            'previousRoom' => $rooms[($index - 1 + $rooms->count()) % $rooms->count()],
            'nextRoom' => $rooms[($index + 1) % $rooms->count()],
        ]);
    }

    public function facilities(Request $request, string $hotelSlug): View
    {
        [$hotel, $locale] = $this->resolveStage($request, $hotelSlug);

        return view('hotel.facilities-index', $this->stageData($hotel, $locale, 'facilities'));
    }

    public function solutions(Request $request, string $hotelSlug): View
    {
        [$hotel, $locale] = $this->resolveStage($request, $hotelSlug);

        return view('hotel.facilities-index', $this->stageData($hotel, $locale, 'solutions'));
    }

    public function portfolio(Request $request, string $hotelSlug): View
    {
        [$hotel, $locale] = $this->resolveStage($request, $hotelSlug);

        return view('hotel.facilities-index', $this->stageData($hotel, $locale, 'portfolio'));
    }

    public function facility(Request $request, string $hotelSlug, int $facilityId): View
    {
        [$hotel, $locale] = $this->resolveStage($request, $hotelSlug);

        $item = $hotel->knowledgeItems()->where('is_active', true)->whereKey($facilityId)->first();
        $sectionKey = $item ? $this->sectionOf($item->category) : null;

        abort_if($sectionKey === null, 404);

        $data = $this->stageData($hotel, $locale, 'facility', $sectionKey);
        $facilities = $data['facilities'];
        $index = $facilities->search(fn (HotelKnowledgeItem $item) => $item->id === $facilityId);

        abort_if($index === false, 404);

        return view('hotel.facility-detail', [
            ...$data,
            'facility' => $facilities[$index],
            'facilityIndex' => $index,
            'previousFacility' => $facilities[($index - 1 + $facilities->count()) % $facilities->count()],
            'nextFacility' => $facilities[($index + 1) % $facilities->count()],
        ]);
    }

    public function info(Request $request, string $hotelSlug): View
    {
        [$hotel, $locale] = $this->resolveStage($request, $hotelSlug);

        return view('hotel.info-index', $this->stageData($hotel, $locale, 'info'));
    }

    public function staff(Request $request, string $hotelSlug): View
    {
        [$hotel, $locale] = $this->resolveStage($request, $hotelSlug);

        return view('hotel.staff-index', $this->stageData($hotel, $locale, 'staff'));
    }

    public function reservationScene(Request $request, string $hotelSlug): View
    {
        [$hotel, $locale] = $this->resolveStage($request, $hotelSlug);

        return view('hotel.reservation-index', [
            ...$this->stageData($hotel, $locale, 'reservation'),
            'preselectedRoom' => (string) $request->query('room', ''),
        ]);
    }

    /**
     * What a signed-in member does not have to type again in the reservation form.
     *
     * @return array{name?: string, phone?: string, contact_type?: string, contact_value?: string}
     */
    private function memberPrefill(Hotel $hotel): array
    {
        $member = $this->memberBookings->signedIn($hotel);

        if (! $member) {
            return [];
        }

        return [
            'name' => (string) $member->name,
            'phone' => (string) $member->phone,
            'contact_type' => filled($member->phone) ? 'whatsapp' : 'email',
            'contact_value' => filled($member->phone) ? $member->phone : $member->email,
        ];
    }

    /**
     * @return array{0: Hotel, 1: string}
     */
    private function resolveStage(Request $request, string $hotelSlug): array
    {
        $hotel = Hotel::where('slug', $hotelSlug)->firstOrFail();

        abort_if(! $hotel->isPublished(), 404);

        $locale = in_array($request->query('lang'), self::SUPPORTED_LOCALES, true)
            ? $request->query('lang')
            : $hotel->default_locale;

        return [$hotel, $locale];
    }

    /**
     * Everything the shared stage shell needs, for whichever scene the guest
     * has walked into.
     *
     * @return array<string, mixed>
     */
    private function sectionOf(string $category): ?string
    {
        foreach (self::SECTIONS as $key => $section) {
            if (in_array($category, $section['categories'], true)) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Names the list a detail page belongs to, so its counter, back link and
     * "ask" button follow the room the visitor came from.
     *
     * @param  array<string, string>  $labels
     * @param  array<string, string>  $lobby
     * @return array{key: string, route: string, heading: string, counter: string, back: string, ask: string, ask_q: string}
     */
    private function sectionMeta(string $key, array $labels, array $lobby): array
    {
        return match ($key) {
            'solutions' => ['key' => 'solutions', 'route' => self::SECTIONS['solutions']['route'], 'heading' => $labels['menu_solutions'], 'counter' => $lobby['counter_solutions'], 'back' => $lobby['back_solutions'], 'ask' => $lobby['ask_solutions'], 'ask_q' => $lobby['ask_solutions_q']],
            'portfolio' => ['key' => 'portfolio', 'route' => self::SECTIONS['portfolio']['route'], 'heading' => $labels['menu_portfolio'], 'counter' => $lobby['counter_portfolio'], 'back' => $lobby['back_portfolio'], 'ask' => $lobby['ask_portfolio'], 'ask_q' => $lobby['ask_portfolio_q']],
            default => ['key' => 'facilities', 'route' => self::SECTIONS['facilities']['route'], 'heading' => $labels['menu_facilities'], 'counter' => $lobby['facility_counter'], 'back' => $lobby['back_facilities'], 'ask' => $lobby['ask_facility'], 'ask_q' => $lobby['ask_facility_q']],
        };
    }

    private function stageData(Hotel $hotel, string $locale, string $scene, ?string $section = null): array
    {
        $sectionKey = $section ?? (isset(self::SECTIONS[$scene]) ? $scene : 'facilities');

        $roomTypes = $hotel->roomTypes()
            ->where('is_active', true)
            ->with(['images' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get()
            ->values();

        $facilities = $hotel->knowledgeItems()->where('is_active', true)
            ->whereIn('category', self::SECTIONS[$sectionKey]['categories'])
            ->orderBy('sort_order')->get()->values();

        $labels = $this->labels($locale);
        $lobby = $this->lobbyLabels($locale);

        return [
            'hotel' => $hotel,
            'roomTypes' => $roomTypes,
            'locale' => $locale,
            'scene' => $scene,
            'backdrop' => $this->sceneBackdrop($scene === 'facility' ? $sectionKey : $scene),
            'menuItems' => $this->stageMenu($hotel, $locale, $labels, $lobby, $this->currentMenuKey($scene, $sectionKey)),
            'section' => $this->sectionMeta($sectionKey, $labels, $lobby),
            'supportedLocales' => self::SUPPORTED_LOCALES,
            'labels' => $labels,
            'lobby' => $lobby,
            'roomTerms' => self::ROOM_TERMS[$locale],
            'wizard' => $this->wizardLabels($locale),
            'tourStops' => $this->tourStops($hotel, $roomTypes, $locale),
            'narration' => self::NARRATION[$locale],
            'roomNarrations' => $this->roomNarrations($hotel, $roomTypes, $locale),
            'staffLinks' => $this->handover->forStaff($hotel, $locale),
            'today' => now($hotel->timezone)->toDateString(),
            'prefill' => $this->memberPrefill($hotel),
            'infoItems' => $hotel->knowledgeItems()->where('is_active', true)
                ->whereIn('category', ['general', 'policies', 'faq'])
                ->orderBy('sort_order')->get()->groupBy('category'),
            'facilities' => $facilities,
            'sceneNarrations' => $this->sceneNarrations($hotel, $facilities, $locale, $sectionKey),
        ];
    }

    /**
     * The stops of the concierge's guided tour: the rooms, a couple of them in
     * turn, then the facilities. `key` is the narrator the scene plays and
     * `spot` the part of that scene the tour draws the guest's eye to.
     *
     * @param  Collection<int, RoomType>  $roomTypes
     * @return list<array{key: string, url: string, line: string, spot: ?string}>
     */
    private function tourStops(Hotel $hotel, Collection $roomTypes, string $locale): array
    {
        $tour = self::NARRATION[$locale];
        $link = fn (string $route, array $parameters = []) => route($route, ['hotelSlug' => $hotel->slug, ...$parameters, 'lang' => $locale]);

        $stops = [['key' => 'rooms', 'url' => $link('hotel.rooms'), 'line' => $tour['tour_rooms'], 'spot' => '.room-nav']];

        foreach ($roomTypes->take(2) as $roomType) {
            $stops[] = [
                'key' => 'room-'.$roomType->slug,
                'url' => $link('hotel.room', ['roomSlug' => $roomType->slug]),
                'line' => str_replace(':name', $roomType->translatedName($locale), $tour['tour_room']),
                'spot' => null,
            ];
        }

        $stops[] = ['key' => 'facilities', 'url' => $link('hotel.facilities'), 'line' => $tour['tour_facilities'], 'spot' => '.facility-grid'];

        return $stops;
    }

    /**
     * Artwork for a scene. A hotel can supply either a single picture with
     * the concierge already in it, or — preferred — a plain background plus
     * one cut-out of her (transparent PNG/WebP) that is layered in front, so
     * she can be placed clear of the panels and reused across scenes.
     *
     * @return array{image: string, focus: string, focusMobile: string, character: ?string, anchor: string, text: string, avatarImage: string, avatarZoom: string, avatarFocus: string}
     */
    private function sceneBackdrop(string $scene): array
    {
        $scenes = [
            'rooms' => [
                'combined' => 'images/suite.webp',
                'background' => 'images/rooms-bg.png',
                'focus' => 'center 26%',
                'focusMobile' => '50% 14%',
                'anchor' => 'right',
                'text' => 'top',
                'avatarZoom' => '500%',
                'avatarFocus' => '52% 10%',
            ],
            'facilities' => [
                'combined' => 'images/facility.webp',
                'background' => 'images/facilities-bg.png',
                'focus' => 'center 30%',
                'focusMobile' => '24% 18%',
                // She stands on the left of this artwork, so the scene text
                // sits low and leaves her face clear.
                'anchor' => 'left',
                'text' => 'bottom',
                'avatarZoom' => '500%',
                'avatarFocus' => '17% 11%',
            ],
            'solutions' => [
                'combined' => 'images/facility.webp',
                'background' => 'images/solutions-bg.webp',
                'focus' => 'center 30%',
                'focusMobile' => '24% 18%',
                'anchor' => 'left',
                'text' => 'bottom',
                'avatarZoom' => '500%',
                'avatarFocus' => '17% 11%',
            ],
            'portfolio' => [
                // Placeholder until a portfolio-room background is supplied: she stands on the left, the list on the right.
                'combined' => 'images/facility.webp',
                'background' => 'images/portfolio-bg.webp',
                'focus' => 'center 30%',
                'focusMobile' => '24% 18%',
                'anchor' => 'left',
                'text' => 'bottom',
                'avatarZoom' => '500%',
                'avatarFocus' => '17% 11%',
            ],
            'info' => [
                // A plain terrace backdrop with her greeting cut-out layered
                // in front, standing clear on the right of the panel.
                'combined' => 'images/information.webp',
                'background' => 'images/information.webp',
                'character' => 'images/character/character greeting.webp',
                'focus' => 'center 38%',
                'focusMobile' => '70% 30%',
                'anchor' => 'right',
                'text' => 'bottom',
                'avatarZoom' => '300%',
                'avatarFocus' => '50% 25%',
            ],
            'staff' => [
                // A reception lounge backdrop with her grateful cut-out
                // centred, so the panel floats low and leaves her clear.
                'combined' => 'images/talk to staff.webp',
                'background' => 'images/talk to staff.webp',
                'character' => 'images/character/character grateful.webp',
                'focus' => 'center 30%',
                'focusMobile' => '62% 20%',
                'anchor' => 'center',
                'text' => 'top',
                'avatarZoom' => '260%',
                'avatarFocus' => '50% 12%',
            ],
            'reservation' => [
                // She stands on the left holding a tablet with a booking
                // form, echoing the wizard panel that sits beside her.
                'combined' => 'images/reservation.webp',
                'background' => 'images/reservation.webp',
                'character' => 'images/character/character reservation.webp',
                'focus' => 'center 30%',
                'focusMobile' => '24% 18%',
                'anchor' => 'left',
                'text' => 'bottom',
                'avatarZoom' => '300%',
                'avatarFocus' => '42% 14%',
            ],
            'lobby' => [
                // The FTS office lobby, with her presenting from the right.
                'combined' => 'images/concierge-lobby.webp',
                'background' => 'images/lobby-bg.webp',
                'character' => 'images/character/character right hand.webp',
                'focus' => 'center 45%',
                'focusMobile' => '30% 50%',
                'anchor' => 'right',
                'text' => 'bottom',
                'avatarZoom' => '300%',
                'avatarFocus' => '69% 20%',
            ],
        ];

        $artwork = $scenes[['room' => 'rooms', 'facility' => 'facilities'][$scene] ?? $scene] ?? $scenes['lobby'];

        $characterImage = $artwork['character'] ?? self::CHARACTER_IMAGE;
        $character = file_exists(public_path($characterImage)) ? $characterImage : null;
        $background = $character && file_exists(public_path($artwork['background'])) ? $artwork['background'] : null;

        if ($background === null) {
            // No separate background for this scene, so use the picture that
            // already has her in it — and do not layer her on top of herself.
            $character = null;
            $background = file_exists(public_path($artwork['combined'])) ? $artwork['combined'] : $scenes['lobby']['combined'];
        }

        return [
            'image' => asset($background),
            'focus' => $artwork['focus'],
            'focusMobile' => $artwork['focusMobile'],
            'character' => $character ? asset($character) : null,
            'anchor' => $artwork['anchor'],
            'text' => $artwork['text'],
            // The avatars crop her face out of whichever picture holds her —
            // the cut-out when one is layered in, otherwise the background.
            'avatarImage' => asset($character ?? $background),
            'avatarZoom' => $artwork['avatarZoom'],
            'avatarFocus' => $artwork['avatarFocus'],
        ];
    }

    /**
     * The main menu. Sections that live on another page are marked so the
     * stage can fade out, as if the concierge were walking the guest over.
     *
     * @param  array<string, string>  $labels
     * @param  array<string, string>  $lobby
     * @return list<array{key: string, label: string, href: string, exit: bool, tour: ?string, topic: string}>
     */
    private function currentMenuKey(string $scene, string $sectionKey): string
    {
        return match ($scene) {
            'room' => 'rooms',
            'facility' => $sectionKey,
            default => $scene,
        };
    }

    private function stageMenu(Hotel $hotel, string $locale, array $labels, array $lobby, string $current = ''): array
    {
        $solutionsUrl = route('hotel.solutions', ['hotelSlug' => $hotel->slug, 'lang' => $locale]);
        $portfolioUrl = route('hotel.portfolio', ['hotelSlug' => $hotel->slug, 'lang' => $locale]);
        $roomsUrl = route('hotel.rooms', ['hotelSlug' => $hotel->slug, 'lang' => $locale]);
        $tour = self::NARRATION[$locale];

        $facilitiesUrl = route('hotel.facilities', ['hotelSlug' => $hotel->slug, 'lang' => $locale]);
        $infoUrl = route('hotel.info', ['hotelSlug' => $hotel->slug, 'lang' => $locale]);
        $reservationUrl = route('hotel.reservation', ['hotelSlug' => $hotel->slug, 'lang' => $locale]);
        $staffUrl = route('hotel.staff', ['hotelSlug' => $hotel->slug, 'lang' => $locale]);

        return array_map(fn (array $item) => [...$item, 'current' => $item['key'] === $current], [
            ['key' => 'rooms', 'label' => $labels['rooms_heading'], 'href' => $roomsUrl, 'exit' => true, 'tour' => $tour['tour_rooms'], 'topic' => $labels['menu_rooms_q']],
            ['key' => 'facilities', 'label' => $labels['menu_facilities'], 'href' => $facilitiesUrl, 'exit' => true, 'tour' => $tour['tour_facilities'], 'topic' => $labels['menu_facilities_q']],
            ['key' => 'solutions', 'label' => $labels['menu_solutions'], 'href' => $solutionsUrl, 'exit' => true, 'tour' => $tour['tour_solutions'], 'topic' => $labels['menu_solutions_q']],
            ['key' => 'portfolio', 'label' => $labels['menu_portfolio'], 'href' => $portfolioUrl, 'exit' => true, 'tour' => $tour['tour_portfolio'], 'topic' => $labels['menu_portfolio_q']],
            ['key' => 'info', 'label' => $lobby['menu_info'], 'href' => $infoUrl, 'exit' => true, 'tour' => $tour['tour_info'], 'topic' => $labels['menu_policies_q']],
            ['key' => 'reservation', 'label' => $lobby['reservation'], 'href' => $reservationUrl, 'exit' => true, 'tour' => $tour['tour_reservation'], 'topic' => $lobby['reservation_q']],
            ['key' => 'staff', 'label' => $labels['menu_staff'], 'href' => $staffUrl, 'exit' => true, 'tour' => $tour['tour_staff'], 'topic' => $labels['menu_staff_q']],
        ]);
    }

    /**
     * Intros for the facilities list and the hotel information scene, built
     * only from stored hotel data.
     *
     * @param  Collection<int, HotelKnowledgeItem>  $facilities
     * @return array{facilities: ?string, info: string}
     */
    private function sceneNarrations(Hotel $hotel, Collection $facilities, string $locale, string $sectionKey = 'facilities'): array
    {
        $templates = self::NARRATION[$locale];
        $separator = $locale === 'ja' ? '、' : '; ';
        $time = fn (?string $value) => $value ? substr($value, 0, 5) : null;

        $intro = null;

        if ($facilities->isNotEmpty()) {
            $examples = $facilities->take(3)->map(fn ($item) => $item->translatedTitle($locale))->implode($separator);
            $intro = str_replace(
                [':count', ':examples'],
                [(string) $facilities->count(), $examples],
                $templates[$sectionKey.($facilities->count() === 1 ? '_one' : '')]
            );
        }

        $location = collect([$hotel->city, $hotel->country])->filter()->implode($locale === 'ja' ? '、' : ', ');
        $parts = [str_replace(':hotel', $hotel->name, $templates['info_welcome'])];

        if ($location !== '') {
            $parts[] = str_replace(':location', $location, $templates['info_place']);
        }

        if ($time($hotel->check_in_time) && $time($hotel->check_out_time)) {
            $parts[] = str_replace([':in', ':out'], [$time($hotel->check_in_time), $time($hotel->check_out_time)], $templates['info_hours']);
        }

        $parts[] = $templates['info_more'];

        return ['facilities' => $intro, 'info' => implode(' ', $parts)];
    }

    /**
     * @param  Collection<int, RoomType>  $roomTypes
     * @return array{rooms: ?string, room: array<string, string>}
     */
    private function roomNarrations(Hotel $hotel, Collection $roomTypes, string $locale): array
    {
        $templates = self::NARRATION[$locale];
        $terms = self::ROOM_TERMS[$locale];
        $money = fn ($value) => $hotel->currency.' '.number_format((float) $value, 0, ',', '.');
        $sentence = fn (string $text) => preg_match('/[.!?。！？]$/u', $text) ? $text : $text.($locale === 'ja' ? '。' : '.');

        if ($roomTypes->isEmpty()) {
            return ['rooms' => null, 'room' => []];
        }

        $intro = str_replace(
            [':count', ':price'],
            [(string) $roomTypes->count(), $money($roomTypes->min('base_price'))],
            $templates[$roomTypes->count() === 1 ? 'rooms_one' : 'rooms']
        );

        $rooms = $roomTypes->mapWithKeys(function ($roomType) use ($templates, $terms, $money, $sentence, $locale) {
            $parts = [$roomType->translatedName($locale).'.', filled($roomType->translatedDescription($locale)) ? $sentence(trim($roomType->translatedDescription($locale))) : null];

            $parts[] = $roomType->size_sqm
                ? str_replace([':size', ':guests'], [(string) $roomType->size_sqm, (string) $roomType->maxOccupancy()], $templates['size_guests'])
                : str_replace(':guests', (string) $roomType->maxOccupancy(), $templates['guests']);

            if ($roomType->view_type) {
                $parts[] = $sentence($terms['view'][$roomType->view_type] ?? Str::headline($roomType->view_type));
            }

            if ($roomType->breakfast_included) {
                $parts[] = $templates['breakfast'];
            }

            $parts[] = str_replace(':price', $money($roomType->base_price), $templates['price']);

            return [$roomType->slug => implode(' ', array_filter($parts))];
        })->all();

        return ['rooms' => $intro, 'room' => $rooms];
    }

    /**
     * @return array<string, string>
     */
    private function openingLabels(string $locale): array
    {
        return match ($locale) {
            'en' => [
                'welcome' => 'Welcome to', 'tagline' => 'Your AI concierge for web development, AI automation, and offshore development.',
                'enter' => 'Enter :name', 'empty' => 'The virtual lobby is being prepared. Please come back soon.',
                'loading' => 'Preparing the FTS experience…', 'sound_on' => 'Sound on', 'sound_off' => 'Sound off', 'rooms' => 'Explore website plans', 'facilities' => 'See our services', 'reservation' => 'Start a project', 'staff' => 'Talk to our team',
            ],
            'ja' => [
                'welcome' => 'ようこそ', 'tagline' => 'AIコンシェルジュが、Web開発・AI自動化・オフショア開発のご相談をお手伝いします。',
                'enter' => ':name に入る', 'empty' => 'バーチャルロビーは準備中です。しばらくしてからお越しください。',
                'loading' => 'FTSの体験を準備しています…', 'sound_on' => 'サウンドオン', 'sound_off' => 'サウンドオフ', 'rooms' => 'プランを見る', 'facilities' => 'サービスを見る', 'reservation' => 'プロジェクトを相談', 'staff' => 'チームに相談',
            ],
            default => [
                'welcome' => 'Selamat datang di', 'tagline' => 'AI Concierge siap membantu kebutuhan web development, AI automation, dan offshore development Anda.',
                'enter' => 'Masuk ke :name', 'empty' => 'Lobi virtual sedang dipersiapkan. Silakan kembali lagi nanti.',
                'loading' => 'Menyiapkan pengalaman FTS…', 'sound_on' => 'Suara aktif', 'sound_off' => 'Suara mati', 'rooms' => 'Jelajahi paket website', 'facilities' => 'Lihat layanan', 'reservation' => 'Mulai proyek', 'staff' => 'Bicara dengan tim',
            ],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function wizardLabels(string $locale): array
    {
        return [...ReservationController::MESSAGES[$locale], 'member_discount' => MemberLabels::for($locale)['member_discount'], ...match ($locale) {
            'en' => [
                'title' => 'Project request', 'intro' => 'A few quick steps. Final scope and rates are confirmed by our team.',
                'step_of' => 'Step :current of :total', 'steps' => ['Plan & start', 'Your details', 'Summary'], 'period_hint' => 'Each request covers 30 days, the same as one monthly fee.',
                'check_in' => 'Start', 'check_out' => 'End', 'nights' => 'day(s)', 'adults' => 'Users', 'children' => 'Extra users', 'rooms' => 'Websites',
                'choose_room' => 'Choose a plan', 'fits' => 'Up to :count users per plan', 'too_small' => 'Too small for your team',
                'extra_bed' => 'Add an extra bed', 'name' => 'Full name', 'contact_method' => 'How should we contact you?',
                'whatsapp' => 'WhatsApp', 'phone' => 'Phone', 'email' => 'Email', 'contact_value' => 'Number or email address',
                'special' => 'Notes (optional)', 'special_placeholder' => 'e.g. preferred start date, existing website',
                'next' => 'Next', 'back' => 'Back', 'edit' => 'Edit', 'submit' => 'Submit project request', 'sending' => 'Sending…',
                'checking' => 'Checking availability…', 'estimated_total' => 'Estimated total', 'guests' => 'Users', 'room' => 'Plan', 'dates' => 'Period',
                'contact' => 'Contact', 'disclaimer' => 'This is a project request. Final scope and rates are confirmed by the FTS team. No payment is taken now.',
                'available_instead' => 'Available for your period instead:', 'done_title' => 'Request received', 'reference' => 'Your reference',
                'awaiting' => 'Awaiting FTS confirmation', 'done_hint' => 'Send your request to our team to speed up confirmation.',
                'send_whatsapp' => 'Send to WhatsApp', 'call_hotel' => 'Call FTS', 'email_hotel' => 'Email FTS', 'new_request' => 'New request',
                'error_network' => 'Connection problem. Your details are saved — please try again or contact our team.',
                'error_generic' => 'Something went wrong. Please try again or contact our team.', 'select_room' => 'Please choose a plan.',
                'contact_staff' => 'Contact our team',
            ],
            'ja' => [
                'title' => 'プロジェクトのご相談', 'intro' => 'かんたんな手順です。範囲と料金はスタッフが最終確認いたします。',
                'step_of' => 'ステップ :current / :total', 'steps' => ['プランと開始日', 'ご連絡先', '確認'], 'period_hint' => '1件の相談は30日分で、月額1回分に相当します。',
                'check_in' => '開始', 'check_out' => '終了', 'nights' => '日', 'adults' => '利用者', 'children' => '追加の利用者', 'rooms' => 'サイト数',
                'choose_room' => 'プランを選ぶ', 'fits' => '1プランあたり最大:count名', 'too_small' => '人数に対応できません',
                'extra_bed' => 'エキストラベッドを追加', 'name' => 'お名前', 'contact_method' => 'ご連絡方法',
                'whatsapp' => 'WhatsApp', 'phone' => '電話', 'email' => 'メール', 'contact_value' => '番号またはメールアドレス',
                'special' => 'ご要望（任意）', 'special_placeholder' => '例：希望の開始日、既存のWebサイト',
                'next' => '次へ', 'back' => '戻る', 'edit' => '編集', 'submit' => 'プロジェクトの相談を送信', 'sending' => '送信中…',
                'checking' => '空室を確認中…', 'estimated_total' => '概算合計', 'guests' => '利用者', 'room' => 'プラン', 'dates' => '期間',
                'contact' => 'ご連絡先', 'disclaimer' => 'これはプロジェクトのご相談リクエストです。範囲と料金はFTSのスタッフが最終確認します。現時点でお支払いは発生しません。',
                'available_instead' => 'ご希望の期間で空いているプラン：', 'done_title' => 'リクエストを受け付けました', 'reference' => '受付番号',
                'awaiting' => 'FTSの確認待ち', 'done_hint' => 'リクエストをスタッフに送ると、確認がスムーズです。',
                'send_whatsapp' => 'WhatsAppで送る', 'call_hotel' => 'FTSに電話', 'email_hotel' => 'FTSにメール', 'new_request' => '新しいリクエスト',
                'error_network' => '接続に問題があります。入力内容は保存されています。もう一度お試しいただくか、スタッフにご連絡ください。',
                'error_generic' => 'エラーが発生しました。もう一度お試しいただくか、スタッフにご連絡ください。', 'select_room' => 'プランを選択してください。',
                'contact_staff' => 'スタッフに連絡',
            ],
            default => [
                'title' => 'Permintaan proyek', 'intro' => 'Hanya beberapa langkah singkat. Lingkup dan biaya final dikonfirmasi oleh tim kami.',
                'step_of' => 'Langkah :current dari :total', 'steps' => ['Paket & mulai', 'Data Anda', 'Ringkasan'], 'period_hint' => 'Setiap permintaan mencakup 30 hari, sama dengan satu biaya bulanan.',
                'check_in' => 'Mulai', 'check_out' => 'Selesai', 'nights' => 'hari', 'adults' => 'Pengguna', 'children' => 'Pengguna tambahan', 'rooms' => 'Jumlah website',
                'choose_room' => 'Pilih paket', 'fits' => 'Maks. :count pengguna per paket', 'too_small' => 'Tidak cukup untuk tim Anda',
                'extra_bed' => 'Tambah extra bed', 'name' => 'Nama lengkap', 'contact_method' => 'Bagaimana kami menghubungi Anda?',
                'whatsapp' => 'WhatsApp', 'phone' => 'Telepon', 'email' => 'Email', 'contact_value' => 'Nomor atau alamat email',
                'special' => 'Catatan (opsional)', 'special_placeholder' => 'mis. tanggal mulai yang diinginkan, website yang sudah ada',
                'next' => 'Lanjut', 'back' => 'Kembali', 'edit' => 'Ubah', 'submit' => 'Kirim permintaan proyek', 'sending' => 'Mengirim…',
                'checking' => 'Memeriksa ketersediaan…', 'estimated_total' => 'Perkiraan total', 'guests' => 'Pengguna', 'room' => 'Paket', 'dates' => 'Periode',
                'contact' => 'Kontak', 'disclaimer' => 'Ini adalah permintaan proyek. Lingkup dan biaya final dikonfirmasi oleh tim FTS. Belum ada pembayaran yang diambil.',
                'available_instead' => 'Tersedia di periode Anda:', 'done_title' => 'Permintaan diterima', 'reference' => 'Nomor referensi',
                'awaiting' => 'Menunggu konfirmasi FTS', 'done_hint' => 'Kirim permintaan ke tim kami agar konfirmasi lebih cepat.',
                'send_whatsapp' => 'Kirim ke WhatsApp', 'call_hotel' => 'Telepon FTS', 'email_hotel' => 'Email FTS', 'new_request' => 'Permintaan baru',
                'error_network' => 'Koneksi bermasalah. Data Anda tersimpan — coba lagi atau hubungi tim kami.',
                'error_generic' => 'Terjadi kesalahan. Coba lagi atau hubungi tim kami.', 'select_room' => 'Silakan pilih paket.',
                'contact_staff' => 'Hubungi tim kami',
            ],
        }];
    }

    private function lobbyLabels(string $locale): array
    {
        return match ($locale) {
            'en' => [
                'welcome' => 'Welcome to your', 'lobby' => 'virtual lobby',
                'intro' => 'A warm welcome. Practical solutions. Let me take care of the details.',
                'home' => 'Lobby', 'explore' => 'Explore FTS', 'reservation' => 'Start a project',
                'reservation_intro' => 'Tell your AI Concierge about your project and preferred dates. We will help you choose a plan and submit a project request.',
                'reservation_q' => 'I would like to start a project. Please help me choose a plan.',
                'start_booking' => 'Plan my project', 'available' => 'Here to help, 24/7',
                'assistant' => 'Your virtual host', 'illustration' => 'AI illustration',
                'staff_intro' => 'Need a personal touch? Send a message and we will connect you with the FTS team.',
                'empty' => 'Ask your AI Concierge for more information.', 'back' => 'Back to lobby',
                'check_in' => 'Opens', 'check_out' => 'Closes', 'location' => 'Find us',
                'connection_error' => 'Chat could not connect. Please reload to try again.',
                'rooms_empty' => 'Plan information will be available soon. Please ask our team.',
                'menu_info' => 'About FTS', 'info_about' => 'About FTS', 'info_policies' => 'Terms', 'info_faq' => 'Frequently asked questions', 'info_tab_about' => 'About', 'info_tab_faq' => 'FAQ',
                'info_address' => 'Address', 'info_hours' => 'Opening hours', 'info_contact' => 'Contact', 'info_map' => 'Open in Maps', 'info_ask' => 'Ask about FTS',
                'facility_counter' => 'Service', 'prev_facility' => 'Previous', 'next_facility' => 'Next', 'ask_facility' => 'Ask about this service', 'back_facilities' => 'All services', 'open_facility' => 'Read more', 'ask_facility_q' => 'Tell me more about :name.',
                'counter_solutions' => 'Solution', 'back_solutions' => 'All AI solutions', 'ask_solutions' => 'Ask about this solution', 'ask_solutions_q' => 'Tell me more about :name.',
                'counter_portfolio' => 'Project', 'back_portfolio' => 'All projects', 'ask_portfolio' => 'Ask about this project', 'ask_portfolio_q' => 'Tell me more about the :name project.',
                'loading' => 'Preparing the FTS experience…',
                'sound_on' => 'Sound on', 'sound_off' => 'Sound off',
                'room_scene' => 'Plan details', 'room_counter' => 'Plan', 'gallery' => 'Photo gallery', 'photo' => 'Photo',
                'no_photo' => 'Photos coming soon', 'prev_room' => 'Previous plan', 'next_room' => 'Next plan',
                'ask_room' => 'Ask about this plan', 'reserve_room' => 'Request this plan', 'breakfast_excluded' => 'Design & development free', 'breakfast' => 'Included',
                'size' => 'Size', 'bed' => 'Bed', 'guests' => 'Users', 'view' => 'View', 'extra_bed' => 'Extra bed',
                'extra_bed_yes' => 'Available', 'extra_bed_no' => 'Not available', 'amenities' => 'Included',
                'availability_note' => 'Final scope and rates are confirmed by the FTS team.',
                'ask_room_q' => 'Tell me more about the :name.',
            ],
            'ja' => [
                'welcome' => 'ようこそ', 'lobby' => 'バーチャルロビー',
                'intro' => '心を込めたおもてなしで、実用的なソリューションをご提案します。細部はお任せください。',
                'home' => 'ロビー', 'explore' => 'FTSをご案内', 'reservation' => 'プロジェクトのご相談',
                'reservation_intro' => 'プロジェクトの内容とご希望の日程をAIコンシェルジュにお伝えください。プランの選択とご相談リクエストをお手伝いします。',
                'reservation_q' => 'プロジェクトを始めたいです。プランの選び方を教えてください。',
                'start_booking' => 'プロジェクトを計画する', 'available' => '24時間お手伝いします',
                'assistant' => 'バーチャルホスト', 'illustration' => 'AIイラスト',
                'staff_intro' => 'FTSのスタッフがお手伝いします。メッセージを送ってご相談ください。',
                'empty' => '詳しくはAIコンシェルジュにお尋ねください。', 'back' => 'ロビーに戻る',
                'check_in' => '開始', 'check_out' => '終了', 'location' => 'アクセス',
                'connection_error' => 'チャットに接続できませんでした。再読み込みしてください。',
                'rooms_empty' => 'プラン情報は準備中です。スタッフにお尋ねください。',
                'menu_info' => 'FTSについて', 'info_about' => 'FTSについて', 'info_policies' => 'ご利用条件', 'info_faq' => 'よくあるご質問', 'info_tab_about' => 'FTSについて', 'info_tab_faq' => 'FAQ',
                'info_address' => '所在地', 'info_hours' => '営業時間', 'info_contact' => 'お問い合わせ', 'info_map' => '地図で開く', 'info_ask' => 'FTSについて聞く',
                'facility_counter' => 'サービス', 'prev_facility' => '前へ', 'next_facility' => '次へ', 'ask_facility' => 'このサービスについて聞く', 'back_facilities' => 'サービス一覧', 'open_facility' => '詳しく見る', 'ask_facility_q' => ':name について詳しく教えてください。',
                'counter_solutions' => 'ソリューション', 'back_solutions' => 'AIソリューション一覧', 'ask_solutions' => 'このソリューションについて聞く', 'ask_solutions_q' => ':name について詳しく教えてください。',
                'counter_portfolio' => '制作実績', 'back_portfolio' => '実績一覧', 'ask_portfolio' => 'この実績について聞く', 'ask_portfolio_q' => ':name の制作について詳しく教えてください。',
                'loading' => 'FTSの体験を準備しています…',
                'sound_on' => 'サウンドオン', 'sound_off' => 'サウンドオフ',
                'room_scene' => 'プランのご案内', 'room_counter' => 'プラン', 'gallery' => 'フォトギャラリー', 'photo' => '写真',
                'no_photo' => '写真は準備中です', 'prev_room' => '前のプラン', 'next_room' => '次のプラン',
                'ask_room' => 'このプランについて聞く', 'reserve_room' => 'このプランを相談', 'breakfast_excluded' => '設計・開発は無料', 'breakfast' => '含む',
                'size' => '広さ', 'bed' => 'ベッド', 'guests' => '利用者', 'view' => '眺望', 'extra_bed' => 'エキストラベッド',
                'extra_bed_yes' => '利用可', 'extra_bed_no' => '利用不可', 'amenities' => '含まれるもの',
                'availability_note' => '範囲と料金は、FTSのスタッフが最終確認いたします。',
                'ask_room_q' => ':name について詳しく教えてください。',
            ],
            default => [
                'welcome' => 'Selamat datang di', 'lobby' => 'lobi virtual Anda',
                'intro' => 'Sambutan hangat. Solusi yang praktis. Biarkan saya membantu rencana proyek Anda.',
                'home' => 'Beranda', 'explore' => 'Jelajahi FTS', 'reservation' => 'Mulai proyek',
                'reservation_intro' => 'Ceritakan proyek dan tanggal mulai yang Anda inginkan kepada AI Concierge. Kami bantu memilih paket hingga pengajuan permintaan proyek.',
                'reservation_q' => 'Saya ingin memulai proyek. Bantu saya memilih paket.',
                'start_booking' => 'Rencanakan proyek', 'available' => 'Siap membantu, 24 jam',
                'assistant' => 'Resepsionis virtual Anda', 'illustration' => 'Ilustrasi AI',
                'staff_intro' => 'Butuh bantuan langsung? Kirim pesan untuk terhubung dengan tim FTS.',
                'empty' => 'Tanyakan informasi selengkapnya kepada AI Concierge.', 'back' => 'Kembali ke lobi',
                'check_in' => 'Buka', 'check_out' => 'Tutup', 'location' => 'Lokasi kantor',
                'connection_error' => 'Chat belum tersambung. Muat ulang halaman untuk mencoba lagi.',
                'rooms_empty' => 'Informasi paket segera tersedia. Silakan tanyakan kepada tim kami.',
                'menu_info' => 'Tentang FTS', 'info_about' => 'Tentang FTS', 'info_policies' => 'Ketentuan', 'info_faq' => 'Pertanyaan yang sering diajukan', 'info_tab_about' => 'Tentang', 'info_tab_faq' => 'FAQ',
                'info_address' => 'Alamat', 'info_hours' => 'Jam buka', 'info_contact' => 'Kontak', 'info_map' => 'Buka di Maps', 'info_ask' => 'Tanya tentang FTS',
                'facility_counter' => 'Layanan', 'prev_facility' => 'Sebelumnya', 'next_facility' => 'Berikutnya', 'ask_facility' => 'Tanya tentang layanan ini', 'back_facilities' => 'Semua layanan', 'open_facility' => 'Selengkapnya', 'ask_facility_q' => 'Ceritakan lebih banyak tentang :name.',
                'counter_solutions' => 'Solusi', 'back_solutions' => 'Semua solusi AI', 'ask_solutions' => 'Tanya tentang solusi ini', 'ask_solutions_q' => 'Ceritakan lebih banyak tentang :name.',
                'counter_portfolio' => 'Proyek', 'back_portfolio' => 'Semua proyek', 'ask_portfolio' => 'Tanya tentang proyek ini', 'ask_portfolio_q' => 'Ceritakan lebih banyak tentang proyek :name.',
                'loading' => 'Menyiapkan pengalaman FTS…',
                'sound_on' => 'Suara aktif', 'sound_off' => 'Suara mati',
                'room_scene' => 'Detail paket', 'room_counter' => 'Paket', 'gallery' => 'Galeri foto', 'photo' => 'Foto',
                'no_photo' => 'Foto segera tersedia', 'prev_room' => 'Paket sebelumnya', 'next_room' => 'Paket berikutnya',
                'ask_room' => 'Tanya tentang paket ini', 'reserve_room' => 'Ajukan paket ini', 'breakfast_excluded' => 'Desain & pengembangan gratis', 'breakfast' => 'Termasuk',
                'size' => 'Ukuran', 'bed' => 'Tempat tidur', 'guests' => 'Pengguna', 'view' => 'Pemandangan', 'extra_bed' => 'Extra bed',
                'extra_bed_yes' => 'Tersedia', 'extra_bed_no' => 'Tidak tersedia', 'amenities' => 'Termasuk',
                'availability_note' => 'Lingkup dan biaya final dikonfirmasi oleh tim FTS.',
                'ask_room_q' => 'Ceritakan lebih banyak tentang :name.',
            ],
        };
    }

    private function labels(string $locale): array
    {
        return match ($locale) {
            'en' => [
                'from' => 'from',
                'per_night' => '/ month',
                'ask_ai' => 'Ask the AI Concierge',
                'rooms_heading' => 'Website Plans',
                'chat_heading' => 'AI Concierge',
                'chat_subtitle' => 'Ask about plans, services, or your project — available 24/7.',
                'chat_placeholder' => 'Type your message…',
                'chat_send' => 'Send',
                'chat_mic' => 'Speak to the concierge',
                'chat_mic_listening' => 'Listening… tap to stop',
                'chat_mic_denied' => 'Microphone access is blocked. Please allow it in your browser settings.',
                'chat_mic_nospeech' => "I didn't catch that. Please try again.",
                'chat_voice_on' => 'Read replies aloud',
                'chat_voice_off' => 'Stop reading aloud',
                'chat_open' => 'Chat with AI Concierge',
                'chat_close' => 'Close conversation',
                'chat_intro' => 'Hi! I\'m the FTS AI Concierge. Ask me about website plans, services, or your project.',
                'breakfast_included' => 'Breakfast included',
                'max_guests' => 'users',
                'chat_room_only' => 'Design & development free',
                'chat_night' => 'day(s)',
                'chat_no_availability' => 'No availability for that period.',
                'chat_booking_received' => 'Project request received',
                'chat_reference' => 'Ref',
                'chat_view_suffix' => 'view',
                'handed_over' => 'A staff member has joined this conversation and will reply shortly.',
                'chat_status_sent' => 'Message sent',
                'chat_status_waiting' => 'Waiting for staff reply',
                'chat_status_replied' => 'Staff has replied',
                'chat_draft_title' => 'Unsaved message',
                'chat_draft_body' => 'This message has not been sent. Keep it as a draft?',
                'chat_draft_keep' => 'Keep typing',
                'chat_draft_discard' => 'Discard draft',
                'thinking' => 'Thinking…',
                'chat_error' => 'I\'m having trouble responding right now. Please try again or contact our team.', 'chat_slow' => 'You are sending messages very quickly. Please wait a moment and try again.', 'chat_retry' => 'Retry',
                'view_details' => 'View details',
                'room_details_question' => 'Show me more details of :room',
                'book_now' => 'Request this plan',
                'chat_hide_details' => 'Hide details',
                'chat_form_title' => 'Request :room',
                'chat_form_confirm' => 'Confirm request',
                'chat_showing_rooms' => 'Showing :count plans',
                'chat_showing_facilities' => 'Showing :count services',
                'chat_open_map' => 'Open in Maps',
                'chat_directions' => 'Directions',
                'chat_getting_there' => 'Visit our office',
                'chat_showing_photos' => ':count photos',
                'chat_hours' => 'Open',
                'chat_photo_prev' => 'Previous photo',
                'chat_photo_next' => 'Next photo',
                'chat_photo_close' => 'Close photo',
                'quick_facilities_q' => 'Show me your services.',
                'quick_location' => 'Location & map',
                'quick_location_q' => 'Where is the FTS office and how do I get there?',
                'quick_menu' => 'Service details',
                'quick_menu_q' => 'What does each service include?',
                'chat_close_showcase' => 'Close',
                'quick_cheapest' => 'Cheapest plan', 'quick_cheapest_q' => 'Which plan is the cheapest?',
                'quick_family' => 'Bigger website', 'quick_family_q' => 'Which plan suits a bigger website best?',
                'quick_view' => 'Most popular', 'quick_view_q' => 'Which plan is the most popular?',
                'quick_pick_dates' => 'Pick a start date', 'quick_tour' => 'Tour with the AI',
                'picker_title' => 'When would you like to start?', 'picker_message' => 'I would like to start:',
                'picker_pick_start' => 'Choose a start date', 'picker_pick_end' => 'Choose an end date', 'picker_continue' => 'Continue',
                'tour_progress' => 'Tour :current / :total', 'tour_next' => 'Skip ›', 'tour_stop' => 'Stop',
                'tour_done' => 'That was a quick look around. When would you like to start?',
                'chat_add_calendar' => 'Add to calendar', 'chat_share' => 'Share', 'chat_share_text' => 'My project with :hotel: :room, :in → :out. Ref :ref',
                'chat_new' => 'New chat',
                'chat_new_title' => 'Start a new chat?',
                'chat_new_body' => 'The current conversation will be cleared from this screen and the concierge will start fresh.',
                'chat_new_confirm' => 'Start new chat',
                'chat_new_keep' => 'Keep this chat',
                'menu_heading' => 'Start here',
                'menu_rooms' => 'Find a plan',
                'menu_rooms_q' => 'I would like to see the website plans.',
                'menu_facilities' => 'Our services',
                'menu_facilities_q' => 'What services does FTS offer?',
                'menu_solutions' => 'AI Solutions',
                'menu_solutions_q' => 'What AI solutions does FTS offer?',
                'menu_portfolio' => 'Portfolio',
                'menu_portfolio_q' => 'Can I see the projects FTS has built?',
                'menu_policies' => 'Terms & FAQ',
                'menu_policies_q' => 'What are the contract terms and response times?',
                'menu_staff' => 'Talk to our team',
                'menu_staff_q' => 'I would like to speak with the FTS team.',
            ],
            'ja' => [
                'from' => '',
                'per_night' => '〜 / 月',
                'ask_ai' => 'AIコンシェルジュに聞く',
                'rooms_heading' => 'Webサイトプラン',
                'chat_heading' => 'AIコンシェルジュ',
                'chat_subtitle' => 'プラン、サービス、プロジェクトについて24時間いつでもお尋ねください。',
                'chat_placeholder' => 'メッセージを入力…',
                'chat_send' => '送信',
                'chat_mic' => 'コンシェルジュに話しかける',
                'chat_mic_listening' => '聞いています… タップで停止',
                'chat_mic_denied' => 'マイクへのアクセスがブロックされています。ブラウザの設定で許可してください。',
                'chat_mic_nospeech' => '聞き取れませんでした。もう一度お試しください。',
                'chat_voice_on' => '返信を読み上げる',
                'chat_voice_off' => '読み上げを止める',
                'chat_open' => 'AIコンシェルジュに相談',
                'chat_close' => '会話を閉じる',
                'chat_intro' => 'こんにちは。FTSのAIコンシェルジュです。Webサイトのプランやサービス、プロジェクトについて何でもお尋ねください。',
                'breakfast_included' => '朝食付き',
                'max_guests' => '名まで',
                'chat_room_only' => '設計・開発は無料',
                'chat_night' => '日',
                'chat_no_availability' => 'ご希望の期間には空きがありません。',
                'chat_booking_received' => 'プロジェクトの相談を受け付けました',
                'chat_reference' => '受付番号',
                'chat_view_suffix' => 'の眺望',
                'handed_over' => 'スタッフがこの会話に参加しました。まもなく返信いたします。',
                'chat_status_sent' => 'メッセージを送信しました',
                'chat_status_waiting' => 'スタッフの返信を待っています',
                'chat_status_replied' => 'スタッフが返信しました',
                'chat_draft_title' => '未送信のメッセージ',
                'chat_draft_body' => 'このメッセージはまだ送信されていません。下書きとして残しますか？',
                'chat_draft_keep' => '入力を続ける',
                'chat_draft_discard' => '下書きを破棄',
                'thinking' => '入力中…',
                'chat_error' => '現在うまくお答えできません。もう一度お試しいただくか、スタッフにご連絡ください。', 'chat_slow' => 'メッセージが多すぎます。少し待ってからもう一度お試しください。', 'chat_retry' => '再試行',
                'view_details' => '詳細を見る',
                'room_details_question' => ':room の詳細を見せてください',
                'book_now' => 'このプランを相談',
                'chat_hide_details' => '詳細を閉じる',
                'chat_form_title' => ':room を相談',
                'chat_form_confirm' => '相談を確定する',
                'chat_showing_rooms' => ':count プランをご案内中',
                'chat_showing_facilities' => ':count 件のサービスをご案内中',
                'chat_open_map' => 'マップで開く',
                'chat_directions' => '経路を見る',
                'chat_getting_there' => 'オフィスへのアクセス',
                'chat_showing_photos' => ':count 枚の写真',
                'chat_hours' => '営業時間',
                'chat_photo_prev' => '前の写真',
                'chat_photo_next' => '次の写真',
                'chat_photo_close' => '写真を閉じる',
                'quick_facilities_q' => 'サービスを見せてください。',
                'quick_location' => '所在地・地図',
                'quick_location_q' => 'FTSのオフィスはどこにあり、どう行けばいいですか？',
                'quick_menu' => 'サービスの内容',
                'quick_menu_q' => '各サービスには何が含まれますか？',
                'chat_close_showcase' => '閉じる',
                'quick_cheapest' => '一番安いプラン', 'quick_cheapest_q' => '一番安いプランはどれですか？',
                'quick_family' => '大きめのサイト', 'quick_family_q' => '大きめのサイトにはどのプランが合いますか？',
                'quick_view' => '一番人気', 'quick_view_q' => '一番人気のプランはどれですか？',
                'quick_pick_dates' => '開始日を選ぶ', 'quick_tour' => 'AIと館内ツアー',
                'picker_title' => 'いつ頃から始めたいですか？', 'picker_message' => '開始を希望します：',
                'picker_pick_start' => '開始日を選択', 'picker_pick_end' => '終了日を選択', 'picker_continue' => '次へ',
                'tour_progress' => 'ツアー :current / :total', 'tour_next' => 'スキップ ›', 'tour_stop' => '終了',
                'tour_done' => 'ご案内は以上です。いつ頃から始めたいですか？',
                'chat_add_calendar' => 'カレンダーに追加', 'chat_share' => '共有', 'chat_share_text' => ':hotel とのプロジェクト：:room、:in → :out。受付番号 :ref',
                'chat_new' => '新しいチャット',
                'chat_new_title' => '新しいチャットを始めますか？',
                'chat_new_body' => '現在の会話はこの画面から消去され、コンシェルジュが最初からご案内します。',
                'chat_new_confirm' => '新しく始める',
                'chat_new_keep' => 'このまま続ける',
                'menu_heading' => 'ここから始める',
                'menu_rooms' => 'プランを探す',
                'menu_rooms_q' => 'Webサイトのプランを見せてください。',
                'menu_facilities' => 'サービス',
                'menu_facilities_q' => 'FTSにはどんなサービスがありますか？',
                'menu_solutions' => 'AIソリューション',
                'menu_solutions_q' => 'FTSにはどんなAIソリューションがありますか？',
                'menu_portfolio' => '制作実績',
                'menu_portfolio_q' => 'FTSがこれまでに手がけたプロジェクトを見せてください。',
                'menu_policies' => '条件・よくある質問',
                'menu_policies_q' => '契約条件と対応時間を教えてください。',
                'menu_staff' => 'チームに相談',
                'menu_staff_q' => 'FTSのスタッフと話したいです。',
            ],
            default => [
                'from' => 'mulai dari',
                'per_night' => '/ bulan',
                'ask_ai' => 'Tanya AI Concierge',
                'rooms_heading' => 'Paket Website',
                'chat_heading' => 'AI Concierge',
                'chat_subtitle' => 'Tanyakan paket, layanan, atau proyek Anda — siap 24 jam.',
                'chat_placeholder' => 'Tulis pesan Anda…',
                'chat_send' => 'Kirim',
                'chat_mic' => 'Bicara dengan concierge',
                'chat_mic_listening' => 'Mendengarkan… ketuk untuk berhenti',
                'chat_mic_denied' => 'Akses mikrofon diblokir. Mohon izinkan di pengaturan browser Anda.',
                'chat_mic_nospeech' => 'Suara tidak terdengar. Silakan coba lagi.',
                'chat_voice_on' => 'Bacakan jawaban',
                'chat_voice_off' => 'Hentikan suara',
                'chat_open' => 'Chat dengan AI Concierge',
                'chat_close' => 'Tutup percakapan',
                'chat_intro' => 'Halo! Saya AI Concierge FTS. Tanyakan apa saja soal paket website, layanan, atau proyek Anda.',
                'breakfast_included' => 'Termasuk sarapan',
                'max_guests' => 'pengguna',
                'chat_room_only' => 'Desain & pengembangan gratis',
                'chat_night' => 'hari',
                'chat_no_availability' => 'Tidak ada slot tersedia untuk periode tersebut.',
                'chat_booking_received' => 'Permintaan proyek diterima',
                'chat_reference' => 'Ref',
                'chat_view_suffix' => 'pemandangan',
                'handed_over' => 'Staf kami telah bergabung dalam percakapan ini dan akan segera membalas.',
                'chat_status_sent' => 'Pesan terkirim',
                'chat_status_waiting' => 'Menunggu balasan staf',
                'chat_status_replied' => 'Staf telah membalas',
                'chat_draft_title' => 'Pesan belum dikirim',
                'chat_draft_body' => 'Pesan ini belum dikirim. Simpan sebagai draft?',
                'chat_draft_keep' => 'Lanjut mengetik',
                'chat_draft_discard' => 'Buang draft',
                'thinking' => 'Sedang mengetik…',
                'chat_error' => 'Saya sedang kesulitan menjawab. Silakan coba lagi atau hubungi tim kami.', 'chat_slow' => 'Pesan terlalu cepat. Mohon tunggu sebentar lalu coba lagi.', 'chat_retry' => 'Coba lagi',
                'view_details' => 'Lihat detail',
                'room_details_question' => 'Tunjukkan detail lengkap :room',
                'book_now' => 'Ajukan paket ini',
                'chat_hide_details' => 'Tutup detail',
                'chat_form_title' => 'Ajukan :room',
                'chat_form_confirm' => 'Konfirmasi permintaan',
                'chat_showing_rooms' => 'Menampilkan :count paket',
                'chat_showing_facilities' => 'Menampilkan :count layanan',
                'chat_open_map' => 'Buka di Maps',
                'chat_directions' => 'Petunjuk arah',
                'chat_getting_there' => 'Kunjungi kantor kami',
                'chat_showing_photos' => ':count foto',
                'chat_hours' => 'Buka',
                'chat_photo_prev' => 'Foto sebelumnya',
                'chat_photo_next' => 'Foto berikutnya',
                'chat_photo_close' => 'Tutup foto',
                'quick_facilities_q' => 'Tunjukkan layanan FTS.',
                'quick_location' => 'Lokasi & peta',
                'quick_location_q' => 'Di mana kantor FTS dan bagaimana cara ke sana?',
                'quick_menu' => 'Detail layanan',
                'quick_menu_q' => 'Apa saja yang termasuk di setiap layanan?',
                'chat_close_showcase' => 'Tutup',
                'quick_cheapest' => 'Paket termurah', 'quick_cheapest_q' => 'Paket mana yang paling murah?',
                'quick_family' => 'Website lebih besar', 'quick_family_q' => 'Paket mana yang cocok untuk website yang lebih besar?',
                'quick_view' => 'Paling populer', 'quick_view_q' => 'Paket mana yang paling populer?',
                'quick_pick_dates' => 'Pilih tanggal mulai', 'quick_tour' => 'Tur bersama AI',
                'picker_title' => 'Kapan Anda ingin mulai?', 'picker_message' => 'Saya ingin mulai:',
                'picker_pick_start' => 'Pilih tanggal mulai', 'picker_pick_end' => 'Pilih tanggal selesai', 'picker_continue' => 'Lanjut',
                'tour_progress' => 'Tur :current / :total', 'tour_next' => 'Lewati ›', 'tour_stop' => 'Berhenti',
                'tour_done' => 'Itu tadi sekilas tentang FTS. Kapan Anda ingin mulai?',
                'chat_add_calendar' => 'Tambahkan ke kalender', 'chat_share' => 'Bagikan', 'chat_share_text' => 'Proyek saya dengan :hotel: :room, :in → :out. Ref :ref',
                'chat_new' => 'Chat baru',
                'chat_new_title' => 'Mulai chat baru?',
                'chat_new_body' => 'Percakapan saat ini akan dihapus dari layar dan concierge akan memulai dari awal.',
                'chat_new_confirm' => 'Mulai chat baru',
                'chat_new_keep' => 'Tetap di chat ini',
                'menu_heading' => 'Mulai dari sini',
                'menu_rooms' => 'Cari paket',
                'menu_rooms_q' => 'Saya ingin melihat paket website yang tersedia.',
                'menu_facilities' => 'Layanan kami',
                'menu_facilities_q' => 'Apa saja layanan yang ditawarkan FTS?',
                'menu_solutions' => 'AI Solutions',
                'menu_solutions_q' => 'Solusi AI apa saja yang ditawarkan FTS?',
                'menu_portfolio' => 'Portofolio',
                'menu_portfolio_q' => 'Boleh saya lihat proyek yang pernah dibuat FTS?',
                'menu_policies' => 'Ketentuan & FAQ',
                'menu_policies_q' => 'Apa ketentuan kontrak dan waktu responsnya?',
                'menu_staff' => 'Bicara dengan tim',
                'menu_staff_q' => 'Saya ingin bicara dengan tim FTS.',
            ],
        };
    }
}
