<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_home_renders_the_fts_landing_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Fujiyama Technology Solutions')
            ->assertDontSee('Hotel');
    }

    public function test_hotel_routes_are_gone(): void
    {
        $this->get('/admin')->assertNotFound();
        $this->get('/demo/rooms')->assertNotFound();
    }
}
