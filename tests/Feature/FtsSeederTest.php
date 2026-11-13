<?php

namespace Tests\Feature;

use App\Models\Hotel;
use Database\Seeders\FtsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FtsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_the_fts_company_with_its_contact_details(): void
    {
        $this->seed(FtsSeeder::class);

        $hotel = Hotel::where('slug', 'fts')->firstOrFail();

        $this->assertSame('Fujiyama Technology Solutions', $hotel->name);
        $this->assertSame('info@fts-tech.co.id', $hotel->email);
        $this->assertSame('+62 895 2933 6179', $hotel->phone);
        $this->assertSame('6289529336179', $hotel->whatsapp);
        $this->assertStringContainsString('Neo Soho Mall', $hotel->address);
        $this->assertSame('published', $hotel->public_status);
        $this->assertSame('Asia/Jakarta', $hotel->timezone);
    }

    public function test_the_three_pricing_plans_use_the_prices_from_the_fts_site(): void
    {
        $this->seed(FtsSeeder::class);

        $plans = Hotel::where('slug', 'fts')->firstOrFail()->roomTypes()->orderBy('base_price')->get();

        $this->assertCount(3, $plans);
        $this->assertSame([700000.0, 1200000.0, 1600000.0], $plans->map(fn ($p) => (float) $p->base_price)->all());
        $this->assertSame(['website-5-pages', 'website-10-pages', 'website-15-pages'], $plans->pluck('slug')->all());
    }

    public function test_services_team_policies_and_faq_are_in_the_knowledge_base(): void
    {
        $this->seed(FtsSeeder::class);

        $items = Hotel::where('slug', 'fts')->firstOrFail()->knowledgeItems;

        $services = $items->where('category', 'facilities')->pluck('title')->all();
        foreach (['Web Development', 'Offshore Development', 'Website Restoran'] as $title) {
            $this->assertContains($title, $services);
        }

        $ai = $items->where('category', 'ai')->pluck('title')->all();
        foreach (['AI Automation', 'AI Agent Development', 'Otomasi Bisnis & Digital Marketing'] as $title) {
            $this->assertContains($title, $ai);
        }

        $projects = $items->where('category', 'portfolio');
        $this->assertCount(12, $projects);
        foreach (['EDEPA World', 'FBE - Fujiyama Biomass Energy', 'LookLook', 'FTS Menu', 'ens. Hair Salon'] as $title) {
            $this->assertContains($title, $projects->pluck('title')->all());
        }
        foreach ($projects as $project) {
            $this->assertStringStartsWith('https://fts-tech.co.id/images/portfolio/', (string) $project->image_url, $project->title);
        }

        $general = $items->where('category', 'general')->pluck('title')->implode(' | ');
        foreach (['Yoshihiro Nakagawa', 'Takakazu Kaburaki', 'Naoki Yoshida', 'Takaki Morita'] as $name) {
            $this->assertStringContainsString($name, $general);
        }

        $this->assertGreaterThanOrEqual(3, $items->where('category', 'faq')->count());
        $this->assertGreaterThanOrEqual(3, $items->where('category', 'policies')->count());
    }

    public function test_every_item_has_english_and_japanese_text(): void
    {
        $this->seed(FtsSeeder::class);

        $hotel = Hotel::where('slug', 'fts')->firstOrFail();

        foreach (['en', 'ja'] as $lang) {
            $this->assertNotEmpty($hotel->translatedDescription($lang), "hotel description in {$lang}");

            foreach ($hotel->knowledgeItems as $item) {
                $this->assertNotSame($item->title, '', "title");
                $this->assertNotEmpty($item->translations[$lang]['title'] ?? null, "{$item->title} title in {$lang}");
                $this->assertNotEmpty($item->translations[$lang]['body'] ?? null, "{$item->title} body in {$lang}");
            }

            foreach ($hotel->roomTypes as $plan) {
                $this->assertNotEmpty($plan->translations[$lang]['name'] ?? null, "{$plan->slug} name in {$lang}");
            }
        }
    }

    public function test_a_thirty_day_period_costs_about_one_monthly_fee(): void
    {
        $this->seed(FtsSeeder::class);

        $plan = Hotel::where('slug', 'fts')->firstOrFail()->roomTypes()->where('slug', 'website-10-pages')->firstOrFail();
        $thirtyDays = (float) $plan->inventory()->orderBy('stay_date')->limit(30)->pluck('price')->sum();

        $this->assertEqualsWithDelta(1200000, $thirtyDays, 6000, 'thirty daily prices should add up to about one monthly fee');
    }

    public function test_every_plan_can_be_requested_for_a_thirty_day_period_starting_today(): void
    {
        $this->seed(FtsSeeder::class);

        $today = now('Asia/Jakarta')->startOfDay();

        foreach (['website-5-pages', 'website-10-pages', 'website-15-pages'] as $slug) {
            $this->postJson('/fts/reservation/quote', [
                'room_type_slug' => $slug, 'check_in' => $today->toDateString(), 'check_out' => $today->addDays(30)->toDateString(),
                'adults' => 1, 'children' => 0, 'rooms' => 1, 'locale' => 'id',
            ])->assertOk();
        }
    }

    public function test_seeding_twice_does_not_duplicate_anything(): void
    {
        $this->seed(FtsSeeder::class);
        $this->seed(FtsSeeder::class);

        $hotel = Hotel::where('slug', 'fts')->firstOrFail();

        $this->assertSame(1, Hotel::count());
        $this->assertSame(3, $hotel->roomTypes()->count());
        $this->assertSame($hotel->knowledgeItems()->pluck('title')->unique()->count(), $hotel->knowledgeItems()->count());
    }

    public function test_company_pages_render_with_the_fts_data_in_each_language(): void
    {
        $this->seed(FtsSeeder::class);

        $this->get('/fts?lang=id')->assertOk()->assertSee('Fujiyama Technology Solutions');
        $this->get('/fts/rooms?lang=id')->assertOk()->assertSee('Website Hingga 5 Halaman')->assertSee('700.000');
        $this->get('/fts/rooms?lang=en')->assertOk()->assertSee('Website up to 5 pages');
        $this->get('/fts/rooms?lang=ja')->assertOk()->assertSee('Webサイト 5ページまで');
        $this->get('/fts/facilities?lang=id')->assertOk()->assertSee('Web Development')->assertDontSee('AI Automation');
        $this->get('/fts/solutions?lang=id')->assertOk()->assertSee('AI Automation')->assertSee('AI Agent Development');
        $this->get('/fts/portfolio?lang=id')->assertOk()->assertSee('EDEPA World')->assertSee('Thai Travel');
        $this->get('/fts/info?lang=id')->assertOk()->assertSee('Neo Soho Mall')->assertSee('Yoshihiro Nakagawa');
        $this->get('/fts/staff?lang=id')->assertOk()->assertSee('info@fts-tech.co.id');
    }
}
