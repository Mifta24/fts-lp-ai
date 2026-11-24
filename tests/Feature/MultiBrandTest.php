<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\HotelKnowledgeItem;
use App\Services\Concierge\ContentGuard;
use App\Services\Reservation\ReservationHandover;
use App\Support\Brand;
use Database\Seeders\FtsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MultiBrandTest extends TestCase
{
    use RefreshDatabase;

    private const BRANDS = ['fts', 'edepaworld', 'apricot'];

    private function useBrand(string $key): Brand
    {
        config(['brand.active' => $key]);
        $this->app->forgetInstance(Brand::class);

        return $this->app->make(Brand::class);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function brands(): array
    {
        return array_combine(self::BRANDS, array_map(fn (string $key) => [$key], self::BRANDS));
    }

    public function test_fts_is_the_default_brand(): void
    {
        $this->assertSame('fts', config('brand.active'));

        $brand = $this->app->make(Brand::class);
        $this->assertSame('fts', $brand->key());
        $this->assertSame('FTS', $brand->shortName());
        $this->assertSame('images/fts-mark.png', $brand->logo());
        $this->assertSame([], $brand->profile());
        $this->assertSame([], $brand->knowledge());
    }

    public function test_an_unknown_brand_fails_loudly(): void
    {
        config(['brand.active' => 'nope']);

        $this->expectException(InvalidArgumentException::class);

        Brand::fromConfig();
    }

    #[DataProvider('brands')]
    public function test_every_brand_is_complete(string $key): void
    {
        $brand = $this->useBrand($key);

        $this->assertNotSame('', $brand->displayName());
        $this->assertNotSame('', $brand->aiIdentity());
        $this->assertSame('FTS', $brand->provider());
        $this->assertFileExists(public_path($brand->logo()));
        $this->assertFileExists(public_path($brand->fullLogo()));

        foreach (['character right hand.webp', 'character greeting.webp', 'character grateful.webp'] as $file) {
            $this->assertFileExists(public_path($brand->characterImage('images/character/'.$file)));
        }

        foreach (['en', 'ja', 'id'] as $locale) {
            $this->assertNotSame('', $brand->intro($locale), "{$key} intro {$locale}");
            $this->assertNotSame('', $brand->tagline($locale), "{$key} tagline {$locale}");
        }
    }

    public function test_placeholders_name_the_brand_and_the_provider(): void
    {
        $brand = $this->useBrand('apricot');

        $this->assertSame('Talk to the Apricot team, built by FTS', $brand->fill('Talk to the :brand team, built by :provider'));
        $this->assertSame(['a' => 'Apricot', 'b' => ['c' => 'FTS']], $brand->fill(['a' => ':brand', 'b' => ['c' => ':provider']]));
    }

    public function test_the_fts_site_reads_as_before(): void
    {
        $this->seed(FtsSeeder::class);

        $this->get('/fts?lang=en')->assertOk()
            ->assertSee('Fujiyama Technology Solutions')
            ->assertSee('Japanese quality, Indonesian excellence.')
            ->assertSee('<link rel="icon" type="image/png" href="'.asset('images/fts-mark.png').'">', false)
            ->assertSee(asset('images/character/character right hand.webp'), false)
            ->assertSee('Explore FTS');

        $this->get('/fts/info?lang=ja')->assertOk()->assertSee('FTSについて')->assertSee('09:00');
    }

    #[DataProvider('whiteLabelBrands')]
    public function test_a_white_label_brand_shows_its_own_identity(string $key, string $name, string $phone, string $address): void
    {
        $this->seed(FtsSeeder::class);
        $this->useBrand($key);
        $this->artisan('brand:apply')->assertSuccessful();

        foreach (['en', 'ja', 'id'] as $locale) {
            $this->get("/fts?lang={$locale}")->assertOk()
                ->assertSee($name)
                ->assertSee(asset("images/brands/{$key}/mark.png"), false)
                ->assertDontSee('Fujiyama Technology Solutions')
                ->assertDontSee('Japanese quality, Indonesian excellence.');
        }

        $this->get('/fts/staff?lang=ja')->assertOk()
            ->assertSee($phone)
            ->assertSee($address)
            ->assertSee("https://{$this->officialHost($key)}", false)
            ->assertDontSee('info@fts-tech.co.id')
            ->assertDontSee('+62 895 2933 6179')
            ->assertDontSee('Neo Soho');

        $this->get('/fts/info?lang=en')->assertOk()
            ->assertSee("About {$name}")
            ->assertSee('Technology partner: FTS')
            ->assertDontSee('Yoshihiro Nakagawa')
            ->assertDontSee('Opens');

        // The AI service pictures, demos and the FTS badge on the concierge stay.
        $this->get('/fts/solutions?lang=ja')->assertOk()
            ->assertSee('Cafe AI')
            ->assertSee(asset('images/character-fts-badge/character right hand.webp'), false);
        $this->get('/fts/portfolio?lang=en')->assertOk()->assertSee('technology partner FTS');
    }

    /**
     * @return array<string, array{string, string, string, string}>
     */
    public static function whiteLabelBrands(): array
    {
        return [
            'edepaworld' => ['edepaworld', 'Edepaworld', '042-593-0707', '東京都福生市加美平'],
            'apricot' => ['apricot', 'Apricot', '03-6264-8940', '東京都品川区上大崎3-5-3'],
        ];
    }

    private function officialHost(string $key): string
    {
        return ['edepaworld' => 'edepaworld.co.jp', 'apricot' => 'apricot-j.co.jp'][$key];
    }

    public function test_applying_a_brand_is_idempotent_and_keeps_the_rest_of_the_data(): void
    {
        $this->seed(FtsSeeder::class);
        $hotel = Hotel::where('slug', 'fts')->firstOrFail();
        $itemCount = $hotel->knowledgeItems()->count();
        $roomTypes = $hotel->roomTypes()->pluck('base_price', 'slug')->all();
        $this->useBrand('edepaworld');

        $this->artisan('brand:apply')->assertSuccessful();
        $afterFirst = $hotel->knowledgeItems()->orderBy('id')->get()->map->only(['id', 'title', 'body', 'is_active', 'details'])->all();

        $this->artisan('brand:apply')->expectsOutputToContain('already applied')->assertSuccessful();
        $afterSecond = $hotel->knowledgeItems()->orderBy('id')->get()->map->only(['id', 'title', 'body', 'is_active', 'details'])->all();

        $this->assertSame($afterFirst, $afterSecond);
        $this->assertSame($itemCount, $hotel->knowledgeItems()->count(), 'items are updated or hidden, never deleted');
        $this->assertSame($roomTypes, $hotel->roomTypes()->pluck('base_price', 'slug')->all(), 'plans and prices stay');

        $hotel->refresh();
        $this->assertSame('Edepaworld', $hotel->name);
        $this->assertSame('info@edepaworld.co.jp', $hotel->email);
        $this->assertNull($hotel->whatsapp);
        $this->assertSame('Asia/Tokyo', $hotel->timezone);
        $this->assertSame('fts', $hotel->slug, 'URLs stay the same');

        $this->assertFalse(HotelKnowledgeItem::where('title', 'Nilai-nilai FTS')->firstOrFail()->is_active);
        $renamed = HotelKnowledgeItem::where('title', 'Proyek lebih dari 15 halaman')->firstOrFail();
        $this->assertStringContainsString('Edepaworld memberikan penawaran khusus', $renamed->body);
        $this->assertStringNotContainsString('FTS', $renamed->body);
    }

    public function test_a_dry_run_changes_nothing(): void
    {
        $this->seed(FtsSeeder::class);
        $this->useBrand('apricot');

        $this->artisan('brand:apply', ['--dry-run' => true])->expectsOutputToContain('[dry-run]')->assertSuccessful();

        $this->assertSame('Fujiyama Technology Solutions', Hotel::where('slug', 'fts')->value('name'));
        $this->assertTrue(HotelKnowledgeItem::where('title', 'Nilai-nilai FTS')->value('is_active'));
    }

    public function test_the_fts_brand_never_touches_the_database(): void
    {
        $this->seed(FtsSeeder::class);
        $before = HotelKnowledgeItem::orderBy('id')->get()->toArray();

        $this->artisan('brand:apply')->expectsOutputToContain('already applied')->assertSuccessful();

        $this->assertSame($before, HotelKnowledgeItem::orderBy('id')->get()->toArray());
        $this->assertSame('Fujiyama Technology Solutions', Hotel::where('slug', 'fts')->value('name'));
    }

    public function test_the_concierge_speaks_for_the_active_brand(): void
    {
        config(['services.local_llm.base_url' => 'http://llm.test', 'services.local_llm.api_key' => 'k', 'services.local_llm.model' => 'm']);
        Http::fake(['llm.test/*' => Http::response(['choices' => [['message' => ['content' => 'ok'], 'finish_reason' => 'stop']]])]);
        $this->seed(FtsSeeder::class);
        $this->useBrand('apricot');
        $this->artisan('brand:apply')->assertSuccessful();

        $token = $this->postJson('/fts/concierge/start', ['locale' => 'ja'])->assertOk()->json('guest_token');
        $this->postJson('/fts/concierge/message', ['guest_token' => $token, 'message' => 'こんにちは'])->assertOk();

        $prompt = '';
        Http::assertSent(function (Request $request) use (&$prompt) {
            $prompt = $request['messages'][0]['content'];

            return true;
        });

        $this->assertStringContainsString('You are the AI Concierge for Apricot, 株式会社アプリコット (APRICOT INC.)', $prompt);
        $this->assertStringContainsString('a knowledgeable member of the Apricot team', $prompt);
        $this->assertStringContainsString('were built by FTS, Apricot\'s technology partner', $prompt);
        $this->assertStringNotContainsString('Jakarta, Indonesia that offers', $prompt);

        $this->assertStringContainsString('the Apricot team', app(ContentGuard::class)->refusal('en'));
    }

    public function test_hand_over_links_use_the_brand_name_and_contacts(): void
    {
        $this->seed(FtsSeeder::class);
        $this->useBrand('apricot');
        $this->artisan('brand:apply')->assertSuccessful();
        $hotel = Hotel::where('slug', 'fts')->firstOrFail();

        $links = app(ReservationHandover::class)->forStaff($hotel, 'en');

        $this->assertNull($links['whatsapp_url'], 'Apricot publishes no WhatsApp');
        $this->assertNull($links['email_url'], 'Apricot publishes no email');
        $this->assertSame('tel:0362648940', $links['phone_url']);
    }

    public function test_the_booking_page_names_the_brand(): void
    {
        $this->seed(FtsSeeder::class);
        $this->useBrand('edepaworld');
        $this->artisan('brand:apply')->assertSuccessful();

        $this->get('/fts/booking?lang=en')->assertOk()->assertSee('Edepaworld')->assertDontSee('Back to FTS');
    }
}
