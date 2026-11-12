<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingContentTest extends TestCase
{
    public function test_all_sections_and_service_cards_are_present(): void
    {
        $response = $this->get('/')->assertOk();

        foreach (['home', 'about', 'services', 'how', 'pricing', 'team', 'contact', 'svc-web', 'svc-ai', 'svc-agent', 'svc-offshore'] as $id) {
            $response->assertSee('id="'.$id.'"', false);
        }
    }

    public function test_pricing_shows_the_three_plans_in_rupiah(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Rp 700.000')
            ->assertSee('Rp 1.200.000')
            ->assertSee('Rp 1.600.000')
            ->assertSee('data-idr="700000"', false)
            ->assertSee('data-idr="1200000"', false)
            ->assertSee('data-idr="1600000"', false)
            ->assertSee('data-currency="USD"', false);
    }

    public function test_team_and_contact_details_are_shown(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Yoshihiro Nakagawa')
            ->assertSee('Takakazu Kaburaki')
            ->assertSee('Naoki Yoshida')
            ->assertSee('Takaki Morita')
            ->assertSee('Neo Soho Mall')
            ->assertSee('info@fts-tech.co.id')
            ->assertSee('tel:+6289529336179', false)
            ->assertSee('id="contact-form"', false)
            ->assertSee('id="form-error"', false);
    }

    public function test_every_language_renders_its_own_pricing_and_contact_headings(): void
    {
        foreach (['id', 'en', 'ja'] as $lang) {
            config(['app.locale' => $lang]);
            app()->setLocale($lang);

            $this->get('/')
                ->assertOk()
                ->assertSee(trans('landing.pricing.title', [], $lang))
                ->assertSee(trans('landing.contact.title', [], $lang))
                ->assertSee('<html lang="'.$lang.'"', false);
        }
    }

    public function test_page_has_no_hotel_leftovers(): void
    {
        $this->get('/')->assertOk()->assertDontSee('Hotel')->assertDontSee('hotel');
    }
}
