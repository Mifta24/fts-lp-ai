<?php

namespace Tests\Feature;

use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageNavigationTest extends TestCase
{
    use RefreshDatabase;

    private function navOf(string $html): string
    {
        preg_match('/<nav class="stage-nav".*?<\/nav>/s', $html, $match);

        return $match[0] ?? '';
    }

    public function test_every_scene_has_the_header_navigation_with_all_five_destinations(): void
    {
        Hotel::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published']);

        foreach (['/demo', '/demo/rooms', '/demo/facilities', '/demo/info', '/demo/staff', '/demo/reservation'] as $path) {
            $nav = $this->navOf($this->get($path.'?lang=en')->assertOk()->getContent());

            $this->assertNotSame('', $nav, "{$path} has no header navigation");

            foreach (['rooms', 'facilities', 'info', 'reservation', 'staff'] as $scene) {
                $this->assertStringContainsString('/demo/'.$scene, $nav, "{$path} nav misses {$scene}");
            }
        }
    }

    public function test_the_current_scene_is_marked_in_the_navigation(): void
    {
        Hotel::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published']);

        $nav = $this->navOf($this->get('/demo/info?lang=en')->getContent());

        $this->assertSame(1, substr_count($nav, 'aria-current="page"'));
        $this->assertMatchesRegularExpression('#<a href="[^"]*/demo/info[^"]*"[^>]*aria-current="page"#', $nav);
    }

    public function test_the_lobby_marks_no_destination_as_current(): void
    {
        Hotel::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published']);

        $this->assertStringNotContainsString('aria-current', $this->navOf($this->get('/demo?lang=en')->getContent()));
    }
}
