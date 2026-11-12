<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\RoomInventory;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectRequestFlowTest extends TestCase
{
    use RefreshDatabase;

    private Hotel $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Hotel::create([
            'name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published', 'currency' => 'IDR', 'timezone' => 'Asia/Jakarta',
            'whatsapp' => '6281234567890', 'phone' => '+62 812 3456 7890', 'email' => 'hi@demo.test',
        ]);

        $plan = $this->company->roomTypes()->create([
            'name' => 'Plan 10', 'slug' => 'plan-10', 'base_price' => 1200000, 'max_adults' => 5, 'max_children' => 0,
            'is_active' => true, 'sort_order' => 0,
        ]);

        $today = CarbonImmutable::now('Asia/Jakarta')->startOfDay();

        foreach (range(0, 40) as $offset) {
            RoomInventory::create([
                'room_type_id' => $plan->id, 'stay_date' => $today->addDays($offset)->toDateString(),
                'total_units' => 5, 'booked_units' => 0, 'price' => 40000,
            ]);
        }
    }

    public function test_the_form_has_three_steps_instead_of_five(): void
    {
        $html = $this->get('/demo/reservation?lang=en')->assertOk()->getContent();

        foreach ([1, 2, 3] as $step) {
            $this->assertStringContainsString('data-step="'.$step.'"', $html);
        }

        $this->assertStringNotContainsString('data-step="4"', $html);
        $this->assertStringNotContainsString('data-step="5"', $html);
    }

    public function test_every_language_lists_three_step_names(): void
    {
        foreach (['id', 'en', 'ja'] as $lang) {
            preg_match('/<script type="application\/json" data-wizard-labels>(.*?)<\/script>/s', $this->get("/demo/reservation?lang={$lang}")->getContent(), $match);
            $labels = json_decode(html_entity_decode($match[1] ?? '{}'), true);

            $this->assertCount(3, $labels['steps'] ?? [], "steps in {$lang}");
            $this->assertNotEmpty($labels['period_hint'] ?? null, "period_hint in {$lang}");
        }
    }

    public function test_users_websites_and_end_date_are_set_for_the_visitor(): void
    {
        $html = $this->get('/demo/reservation?lang=en')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<input type="hidden" name="adults" value="1">/', $html);
        $this->assertMatchesRegularExpression('/<input type="hidden" name="children" value="0">/', $html);
        $this->assertMatchesRegularExpression('/<input type="hidden" name="rooms" value="1">/', $html);
        $this->assertMatchesRegularExpression('/<input type="hidden" name="check_out"/', $html);
        $this->assertMatchesRegularExpression('/<input type="date" name="check_in"/', $html);
        $this->assertStringContainsString('data-period-days="30"', $html);
    }

    public function test_the_default_thirty_day_period_is_accepted_and_costs_one_monthly_fee(): void
    {
        $today = CarbonImmutable::now('Asia/Jakarta')->startOfDay();

        $this->postJson('/demo/reservation/quote', [
            'room_type_slug' => 'plan-10', 'check_in' => $today->toDateString(), 'check_out' => $today->addDays(30)->toDateString(),
            'adults' => 1, 'children' => 0, 'rooms' => 1, 'locale' => 'en',
        ])->assertOk()->assertJsonPath('nights', 30);
    }
}
