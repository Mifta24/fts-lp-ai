<?php

namespace Tests\Feature;

use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SceneBackgroundTest extends TestCase
{
    use RefreshDatabase;

    /** Scene path => [installed background, artwork used when that file is missing]. */
    private const SCENES = [
        'reservation' => ['images/consultation-bg.webp', 'images/reservation.webp'],
        'rooms' => ['images/rooms-bg.webp', 'images/suite.webp'],
        'staff' => ['images/staff-bg.webp', 'images/talk to staff.webp'],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $hotel = Hotel::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published', 'currency' => 'IDR']);
        $hotel->roomTypes()->create(['name' => 'Plan', 'slug' => 'plan', 'base_price' => 1000, 'max_adults' => 1, 'max_children' => 0, 'is_active' => true]);
    }

    public function test_the_installed_photos_are_what_each_room_shows(): void
    {
        foreach (self::SCENES as $path => [$background]) {
            $this->assertFileExists(public_path($background), "{$background} is missing");

            $this->get("/demo/{$path}")->assertOk()->assertSee($background, false);
        }
    }

    public function test_a_room_falls_back_to_its_original_artwork_when_its_photo_is_missing(): void
    {
        foreach (self::SCENES as $path => [$background, $fallback]) {
            $file = public_path($background);
            $moved = $file.'.hidden-by-test';

            rename($file, $moved);

            try {
                $this->get("/demo/{$path}")
                    ->assertOk()
                    ->assertDontSee($background, false)
                    ->assertSee($fallback, false);
            } finally {
                rename($moved, $file);
            }
        }
    }
}
