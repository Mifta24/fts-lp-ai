<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingHeroTest extends TestCase
{
    public function test_hero_shows_headline_badge_tiles_and_buttons(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('AI Automation dan Web Development')
            ->assertSee('Kualitas Jepang')
            ->assertSee('Web Development')
            ->assertSee('AI Agent')
            ->assertSee('Offshore Dev')
            ->assertSee('Otomasi Bisnis')
            ->assertSee('Mulai Proyek Anda')
            ->assertSee('Lihat Layanan');
    }

    public function test_hero_has_avatar_logo_and_starfield_canvas(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('images/avatar.webp', false)
            ->assertSee('images/logo.webp', false)
            ->assertSee('id="starfield"', false);
    }

    public function test_header_links_the_three_languages_and_whatsapp(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('?lang=id', false)
            ->assertSee('?lang=en', false)
            ->assertSee('?lang=ja', false)
            ->assertSee('https://wa.me/6289529336179', false);
    }

    public function test_avatar_hides_itself_when_the_image_fails_to_load(): void
    {
        $this->get('/')->assertSee('onerror="this.hidden = true"', false);
    }

    public function test_avatar_and_logo_files_exist(): void
    {
        $this->assertFileExists(public_path('images/avatar.webp'));
        $this->assertFileExists(public_path('images/logo.webp'));
    }
}
