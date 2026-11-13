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

    public function test_the_header_navigation_has_a_glider_for_the_sliding_highlight(): void
    {
        Hotel::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published']);

        $nav = $this->navOf($this->get('/demo/info?lang=en')->getContent());

        $this->assertStringContainsString('data-nav-glider', $nav);
        $this->assertStringContainsString('<span class="stage-nav-glider" aria-hidden="true" data-nav-glider></span>', $nav);
    }

    public function test_every_quick_chip_carries_an_icon_for_its_destination(): void
    {
        Hotel::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published']);

        preg_match('/<ul class="chat-chips".*?<\/ul>/s', $this->get('/demo?lang=en')->assertOk()->getContent(), $match);
        $chips = $match[0] ?? '';

        foreach (['rooms', 'facilities', 'solutions', 'portfolio', 'info', 'reservation', 'staff', 'tour'] as $key) {
            $this->assertMatchesRegularExpression('/<span class="chip-icon" data-icon="'.$key.'"><svg/', $chips, "chip {$key} has no icon");
        }

        $this->assertSame(8, substr_count($chips, 'class="chip-label"'));
    }

    public function test_the_header_has_a_theme_switch_labelled_in_every_language(): void
    {
        Hotel::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published']);

        foreach (['en' => ['Dark theme', 'Light theme'], 'id' => ['Tema gelap', 'Tema terang'], 'ja' => ['ダークテーマ', 'ライトテーマ']] as $lang => [$dark, $light]) {
            $html = $this->get('/demo/info?lang='.$lang)->assertOk()->getContent();

            $this->assertMatchesRegularExpression('/<button type="button" class="sound-toggle theme-toggle" data-theme-toggle data-label-dark="'.$dark.'" data-label-light="'.$light.'" aria-pressed="false" aria-label="'.$dark.'">/', $html, "theme switch in {$lang}");
        }
    }

    public function test_the_saved_theme_is_applied_before_the_page_paints(): void
    {
        Hotel::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published']);

        $head = strstr($this->get('/demo?lang=en')->getContent(), '</head>', true);

        $this->assertStringContainsString("localStorage.getItem('fts_theme')", $head);
        $this->assertStringContainsString('document.documentElement.dataset.theme', $head);
    }
}
