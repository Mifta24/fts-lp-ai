<?php

namespace Tests\Feature;

use Illuminate\Support\Arr;
use Tests\TestCase;

class LangFilesTest extends TestCase
{
    /** @return array<string, string> */
    private function flat(string $lang): array
    {
        return Arr::dot(require lang_path("{$lang}/landing.php"));
    }

    public function test_all_languages_have_identical_keys(): void
    {
        $keys = array_keys($this->flat('id'));
        sort($keys);

        foreach (['en', 'ja'] as $lang) {
            $other = array_keys($this->flat($lang));
            sort($other);
            $this->assertSame($keys, $other, "Keys differ in {$lang}");
        }
    }

    public function test_no_translation_is_empty(): void
    {
        foreach (['id', 'en', 'ja'] as $lang) {
            foreach ($this->flat($lang) as $key => $value) {
                $this->assertNotSame('', trim((string) $value), "{$lang}.{$key} is empty");
            }
        }
    }
}
