<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocaleTest extends TestCase
{
    public function test_default_language_is_indonesian(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<html lang="id"', false)
            ->assertSee('Mulai Proyek Anda');
    }

    public function test_query_selects_a_language_and_remembers_it_in_a_cookie(): void
    {
        $this->get('/?lang=en')
            ->assertOk()
            ->assertSee('<html lang="en"', false)
            ->assertSee('Start Your Project')
            ->assertCookie('fts_lang', 'en', false);

        $this->get('/?lang=ja')
            ->assertOk()
            ->assertSee('<html lang="ja"', false)
            ->assertSee('プロジェクトを始める');
    }

    public function test_unknown_query_value_falls_back_to_indonesian_without_setting_a_cookie(): void
    {
        $this->get('/?lang=xx')
            ->assertOk()
            ->assertSee('<html lang="id"', false)
            ->assertCookieMissing('fts_lang');
    }

    public function test_array_query_value_does_not_break_the_page(): void
    {
        $this->get('/?lang[]=en')
            ->assertOk()
            ->assertSee('<html lang="id"', false);
    }

    public function test_cookie_is_used_when_there_is_no_query(): void
    {
        $this->withUnencryptedCookie('fts_lang', 'en')
            ->get('/')
            ->assertOk()
            ->assertSee('<html lang="en"', false);
    }

    public function test_unknown_cookie_value_is_ignored(): void
    {
        $this->withUnencryptedCookie('fts_lang', 'xx')
            ->get('/')
            ->assertOk()
            ->assertSee('<html lang="id"', false);
    }

    public function test_query_wins_over_cookie(): void
    {
        $this->withUnencryptedCookie('fts_lang', 'en')
            ->get('/?lang=ja')
            ->assertOk()
            ->assertSee('<html lang="ja"', false);
    }

    public function test_pricing_section_exposes_the_usd_rate_from_config(): void
    {
        config(['fts.usd_rate' => 16500]);

        $this->get('/')->assertSee('data-usd-rate="16500"', false);
    }
}
