<?php

namespace Tests\Feature;

use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Hotel::create([
            'name' => 'Demo Co', 'slug' => 'demo', 'public_status' => 'published', 'description' => 'We build AI websites.',
            'address' => 'Jl. Contoh 1', 'city' => 'Jakarta', 'country' => 'Indonesia',
            'phone' => '+62 811 0000 111', 'email' => 'hi@demo.test',
        ]);
        Hotel::create(['name' => 'Hidden Draft', 'slug' => 'draft']);
    }

    /** @return array<string, mixed> */
    private function jsonLd(string $html): array
    {
        preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $match);

        return json_decode($match[1] ?? '{}', true) ?? [];
    }

    public function test_the_lobby_describes_the_company_in_structured_data(): void
    {
        $data = $this->jsonLd($this->get('/demo?lang=en')->assertOk()->getContent());

        $this->assertSame('https://schema.org', $data['@context'] ?? null);
        $this->assertSame('Organization', $data['@type'] ?? null);
        $this->assertSame('Demo Co', $data['name'] ?? null);
        $this->assertSame('We build AI websites.', $data['description'] ?? null);
        $this->assertSame('hi@demo.test', $data['email'] ?? null);
        $this->assertSame('+62 811 0000 111', $data['telephone'] ?? null);
        $this->assertSame('Jl. Contoh 1', $data['address']['streetAddress'] ?? null);
        $this->assertSame('ID', $data['address']['addressCountry'] ?? null);
    }

    public function test_other_rooms_do_not_repeat_the_organization_data(): void
    {
        $this->assertSame([], $this->jsonLd($this->get('/demo/solutions')->assertOk()->getContent()));
    }

    public function test_every_page_has_a_description_and_language_alternates(): void
    {
        $html = $this->get('/demo/portfolio?lang=en')->assertOk()->getContent();

        $this->assertStringContainsString('<meta name="description" content="We build AI websites.">', $html);

        foreach (['id', 'en', 'ja'] as $lang) {
            $this->assertMatchesRegularExpression('#<link rel="alternate" hreflang="'.$lang.'" href="[^"]*/demo/portfolio\?lang='.$lang.'">#', $html);
        }

        $this->assertMatchesRegularExpression('#<link rel="alternate" hreflang="x-default" href="[^"]*/demo/portfolio">#', $html);
        $this->assertMatchesRegularExpression('#<link rel="canonical" href="[^"]*/demo/portfolio">#', $html);
    }

    public function test_the_sitemap_lists_published_companies_with_every_room_and_language(): void
    {
        $response = $this->get('/sitemap.xml')->assertOk();
        $xml = $response->getContent();

        $this->assertStringContainsString('application/xml', (string) $response->headers->get('Content-Type'));
        $this->assertNotFalse(simplexml_load_string($xml), 'sitemap is not valid XML');

        foreach (['', '/rooms', '/facilities', '/solutions', '/portfolio', '/info', '/staff', '/reservation'] as $path) {
            $this->assertStringContainsString('<loc>'.url('/demo'.$path).'</loc>', $xml, "missing /demo{$path}");
        }

        $this->assertStringContainsString('hreflang="ja" href="'.url('/demo/solutions').'?lang=ja"', $xml);
        $this->assertStringNotContainsString('/draft', $xml);
    }

    public function test_robots_points_to_the_sitemap_and_hides_the_admin(): void
    {
        $body = $this->get('/robots.txt')->assertOk()->getContent();

        $this->assertStringContainsString('Sitemap: '.url('/sitemap.xml'), $body);
        $this->assertStringContainsString('Disallow: /admin', $body);
        $this->assertStringContainsString('Disallow: /platform', $body);
    }
}
