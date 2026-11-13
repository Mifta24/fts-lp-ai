<?php

namespace Tests\Feature;

use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatVoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Hotel::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published']);
    }

    public function test_the_chat_header_has_a_voice_toggle_that_starts_hidden_and_off(): void
    {
        $html = $this->get('/demo?lang=en')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<button type="button" data-chat-voice[^>]*aria-pressed="false"[^>]*hidden/s', $html);
    }

    public function test_voice_labels_exist_in_every_language(): void
    {
        $expected = ['id' => 'Bacakan jawaban', 'en' => 'Read replies aloud', 'ja' => '返信を読み上げる'];

        foreach ($expected as $lang => $label) {
            $html = $this->get("/demo?lang={$lang}")->assertOk()->getContent();

            $this->assertStringContainsString('data-label-voice-on="'.$label.'"', $html, "voice-on in {$lang}");
            $this->assertMatchesRegularExpression('/data-label-voice-off="[^"]+"/', $html, "voice-off in {$lang}");
        }
    }

    public function test_the_voice_toggle_is_on_every_scene_that_has_the_chat(): void
    {
        foreach (['/demo/rooms', '/demo/solutions', '/demo/portfolio', '/demo/info', '/demo/reservation'] as $path) {
            $this->get($path.'?lang=en')->assertOk()->assertSee('data-chat-voice', false);
        }
    }
}
