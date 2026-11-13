<?php

namespace Tests\Unit;

use App\Services\Concierge\ReplyText;
use PHPUnit\Framework\TestCase;

class ReplyTextTest extends TestCase
{
    public function test_markdown_emphasis_code_and_headings_are_removed(): void
    {
        $this->assertSame(
            "Paket kami\nWebsite Hingga 5 Halaman mulai Rp 700.000 per bulan, kode BK-1.",
            ReplyText::plain("## Paket kami\n**Website Hingga 5 Halaman** mulai __Rp 700.000__ per bulan, kode `BK-1`.")
        );
    }

    public function test_an_inline_numbered_list_is_put_one_item_per_line(): void
    {
        $this->assertSame(
            "Berikut paketnya:\n1. Website Hingga 5 Halaman - Rp 700.000\n2. Website Hingga 10 Halaman - Rp 1.200.000\n3. Website Hingga 15 Halaman - Rp 1.600.000\nSilakan pilih.",
            ReplyText::plain('Berikut paketnya: 1. **Website Hingga 5 Halaman** - Rp 700.000 2. **Website Hingga 10 Halaman** - Rp 1.200.000 3. **Website Hingga 15 Halaman** - Rp 1.600.000 Silakan pilih.')
        );
    }

    public function test_a_real_concierge_reply_reads_as_a_list(): void
    {
        $reply = 'Berikut adalah paket website yang tersedia di Fujiyama Technology Solutions: 1. **Website Hingga 5 Halaman** - Rp 700.000/bulan (server) 2. **Website Hingga 10 Halaman** - Rp 1.200.000/bulan (server) 3. **Website Hingga 15 Halaman** - Rp 1.600.000/bulan (server) Semua paket sudah termasuk desain & pengembangan website gratis.';

        $this->assertSame(
            "Berikut adalah paket website yang tersedia di Fujiyama Technology Solutions:\n1. Website Hingga 5 Halaman - Rp 700.000/bulan (server)\n2. Website Hingga 10 Halaman - Rp 1.200.000/bulan (server)\n3. Website Hingga 15 Halaman - Rp 1.600.000/bulan (server)\nSemua paket sudah termasuk desain & pengembangan website gratis.",
            ReplyText::plain($reply)
        );
    }

    public function test_dash_and_star_bullets_become_dots(): void
    {
        $this->assertSame("Layanan:\n• Web Development\n• AI Automation", ReplyText::plain("Layanan:\n- Web Development\n* **AI Automation**"));
    }

    public function test_plain_text_numbers_and_prices_are_left_alone(): void
    {
        $text = 'Paket 2 halaman? Harga mulai Rp 1.200.000, kontrak 36 bulan (6 bulan terakhir gratis). Jam 09.00-18.00.';

        $this->assertSame($text, ReplyText::plain($text));
    }

    public function test_blank_lines_are_collapsed_and_ends_trimmed(): void
    {
        $this->assertSame("Halo!\n\nAda yang bisa dibantu?", ReplyText::plain("\n\nHalo!\n\n\n\nAda yang bisa dibantu?  \n"));
    }
}
