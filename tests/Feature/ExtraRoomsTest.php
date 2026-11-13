<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Hotel;
use App\Models\HotelKnowledgeItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExtraRoomsTest extends TestCase
{
    use RefreshDatabase;

    private Hotel $company;

    /** @var array<string, HotelKnowledgeItem> */
    private array $items = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Hotel::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published']);

        foreach ([
            'web' => ['facilities', 'Web Development'],
            'agent' => ['ai', 'AI Agent Development'],
            'automation' => ['ai', 'AI Automation'],
            'edepa' => ['portfolio', 'EDEPA World'],
            'fbe' => ['portfolio', 'FBE Biomass'],
            'about' => ['general', 'About us'],
        ] as $key => [$category, $title]) {
            $this->items[$key] = $this->company->knowledgeItems()->create([
                'category' => $category, 'title' => $title, 'body' => $title.' description', 'is_active' => true, 'sort_order' => count($this->items),
            ]);
        }
    }

    public function test_the_solutions_room_lists_only_ai_items(): void
    {
        $this->get('/demo/solutions?lang=en')
            ->assertOk()
            ->assertSee('data-scene="solutions"', false)
            ->assertSee('AI Agent Development')
            ->assertSee('AI Automation')
            ->assertDontSee('Web Development')
            ->assertDontSee('EDEPA World');
    }

    public function test_the_portfolio_room_lists_only_projects(): void
    {
        $this->get('/demo/portfolio?lang=en')
            ->assertOk()
            ->assertSee('data-scene="portfolio"', false)
            ->assertSee('EDEPA World')
            ->assertSee('FBE Biomass')
            ->assertDontSee('AI Automation')
            ->assertDontSee('Web Development');
    }

    public function test_the_facilities_room_no_longer_lists_ai_items_or_projects(): void
    {
        $this->get('/demo/facilities?lang=en')
            ->assertOk()
            ->assertSee('Web Development')
            ->assertDontSee('AI Agent Development')
            ->assertDontSee('EDEPA World');
    }

    public function test_detail_pages_work_in_each_room_and_link_back_to_their_own_list(): void
    {
        $this->get('/demo/facilities/'.$this->items['agent']->id.'?lang=en')
            ->assertOk()
            ->assertSee('href="'.route('hotel.solutions', ['hotelSlug' => 'demo', 'lang' => 'en']).'"', false)
            ->assertSee('1 / 2');

        $this->get('/demo/facilities/'.$this->items['fbe']->id.'?lang=en')
            ->assertOk()
            ->assertSee('href="'.route('hotel.portfolio', ['hotelSlug' => 'demo', 'lang' => 'en']).'"', false)
            ->assertSee('2 / 2');

        $this->get('/demo/facilities/'.$this->items['web']->id.'?lang=en')
            ->assertOk()
            ->assertSee('href="'.route('hotel.facilities', ['hotelSlug' => 'demo', 'lang' => 'en']).'"', false);
    }

    public function test_a_demo_entry_links_to_its_live_demo_in_the_visitors_language(): void
    {
        $demo = $this->company->knowledgeItems()->create([
            'category' => 'ai', 'title' => 'Cafe AI', 'body' => 'A live cafe demo.', 'is_active' => true, 'sort_order' => 9,
            'details' => ['demo_url' => 'https://cafe-ai.example.test/cafe-ai'],
        ]);

        $this->assertSame('https://cafe-ai.example.test/cafe-ai?lang=ja', $demo->demoUrl('ja'));

        $this->get('/demo/facilities/'.$demo->id.'?lang=en')
            ->assertOk()
            ->assertSee('href="https://cafe-ai.example.test/cafe-ai?lang=en"', false)
            ->assertSee('target="_blank"', false)
            ->assertSee('rel="noopener"', false)
            ->assertSee('Try the demo');

        $this->get('/demo/facilities/'.$demo->id.'?lang=id')->assertOk()->assertSee('Coba demo');
        $this->get('/demo/facilities/'.$demo->id.'?lang=ja')->assertOk()->assertSee('デモを試す');
    }

    public function test_entries_without_a_demo_have_no_demo_link(): void
    {
        $this->assertNull($this->items['agent']->demoUrl('en'));

        $this->get('/demo/facilities/'.$this->items['agent']->id.'?lang=en')
            ->assertOk()
            ->assertDontSee('Try the demo');
    }

    public function test_items_outside_the_rooms_still_return_not_found(): void
    {
        $this->get('/demo/facilities/'.$this->items['about']->id)->assertNotFound();
    }

    public function test_the_header_navigation_has_both_new_rooms_and_marks_the_current_one(): void
    {
        $nav = function (string $path): string {
            preg_match('/<nav class="stage-nav".*?<\/nav>/s', $this->get($path)->assertOk()->getContent(), $match);

            return $match[0] ?? '';
        };

        $lobby = $nav('/demo?lang=en');
        $this->assertStringContainsString('/demo/solutions', $lobby);
        $this->assertStringContainsString('/demo/portfolio', $lobby);

        $solutions = $nav('/demo/solutions?lang=en');
        $this->assertSame(1, substr_count($solutions, 'aria-current="page"'));
        $this->assertMatchesRegularExpression('#<a href="[^"]*/demo/solutions[^"]*"[^>]*aria-current="page"#', $solutions);

        $detail = $nav('/demo/facilities/'.$this->items['edepa']->id.'?lang=en');
        $this->assertMatchesRegularExpression('#<a href="[^"]*/demo/portfolio[^"]*"[^>]*aria-current="page"#', $detail);
    }

    public function test_the_new_rooms_have_headings_in_every_language(): void
    {
        $this->get('/demo/solutions?lang=id')->assertOk()->assertSee('AI Solutions');
        $this->get('/demo/portfolio?lang=id')->assertOk()->assertSee('Portofolio');
        $this->get('/demo/solutions?lang=ja')->assertOk()->assertSee('AIソリューション');
        $this->get('/demo/portfolio?lang=ja')->assertOk()->assertSee('制作実績');
        $this->get('/demo/portfolio?lang=en')->assertOk()->assertSee('Portfolio');
    }

    public function test_the_chat_may_report_the_new_rooms_as_its_scene(): void
    {
        $this->postJson('/demo/concierge/start', ['locale' => 'en'])->assertOk();

        $token = Conversation::firstOrFail()->guest_token;

        foreach (['solutions', 'portfolio'] as $scene) {
            $this->assertContains($scene, Conversation::SCENES, "{$scene} is not an accepted chat scene");
        }

        $this->assertNotEmpty($token);
    }
}
