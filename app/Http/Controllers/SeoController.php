<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    private const ROOMS = ['', '/rooms', '/facilities', '/solutions', '/portfolio', '/info', '/staff', '/reservation'];

    private const LANGUAGES = ['id', 'en', 'ja'];

    public function sitemap(): Response
    {
        $urls = Hotel::where('public_status', 'published')->orderBy('slug')->get()->flatMap(
            fn (Hotel $company) => array_map(fn (string $room) => url('/'.$company->slug.$room), self::ROOMS)
        );

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n    <loc>{$url}</loc>\n";

            foreach (self::LANGUAGES as $language) {
                $xml .= '    <xhtml:link rel="alternate" hreflang="'.$language.'" href="'.$url.'?lang='.$language."\"/>\n";
            }

            $xml .= '    <xhtml:link rel="alternate" hreflang="x-default" href="'.$url."\"/>\n  </url>\n";
        }

        return response($xml.'</urlset>'."\n", 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        return response(
            "User-agent: *\nDisallow: /admin\nDisallow: /platform\n\nSitemap: ".url('/sitemap.xml')."\n",
            200,
            ['Content-Type' => 'text/plain; charset=UTF-8']
        );
    }
}
