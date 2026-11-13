<?php

namespace App\Services\Concierge;

/**
 * The chat shows replies as plain text, so Markdown the model slips in
 * (bold, code, headings, bullet marks) is taken out and a list written on
 * one line is broken into one item per line.
 */
class ReplyText
{
    public static function plain(string $text): string
    {
        $text = str_replace("\r\n", "\n", $text);

        // Headings, emphasis and inline code keep their words only.
        $text = preg_replace('/^\s{0,3}#{1,6}\s+/m', '', $text);
        $text = preg_replace('/(\*\*|__)(.+?)\1/s', '$2', $text);
        $text = preg_replace('/`([^`\n]+)`/', '$1', $text);

        // "- item" and "* item" bullets become dots.
        $text = preg_replace('/^[ \t]*[-*][ \t]+/m', '• ', $text);

        // A numbered list run together on one line ("… 1. A 2. B 3. C …")
        // gets one item per line, and whatever follows the last item too.
        if (preg_match('/(?:^|\s)1\.\s.+\s2\.\s/s', $text)) {
            $text = preg_replace('/[ \t]+(\d{1,2})\.[ \t]+(?=\S)/', "\n$1. ", $text);
            $text = self::splitAfterLastItem($text);
        }

        $text = preg_replace('/[ \t]+$/m', '', $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        return trim($text);
    }

    /**
     * The sentence after a run-together list sticks to its last item
     * ("3. Plan C - Rp 1.600.000 Silakan pilih."). Past the item's own
     * " - " or ": " separator, a capitalised word right after a number or
     * a closing bracket starts that sentence, so it moves to its own line.
     */
    private static function splitAfterLastItem(string $text): string
    {
        $lines = explode("\n", $text);

        for ($i = count($lines) - 1; $i >= 0; $i--) {
            if (! preg_match('/^\d{1,2}\. /', $lines[$i])) {
                continue;
            }

            $separator = preg_match('/ - |: /', $lines[$i], $found, PREG_OFFSET_CAPTURE) ? $found[0][1] + 2 : null;

            if ($separator !== null && preg_match('/(?<=[\d)])[ \t]+(?=\p{Lu})/u', $lines[$i], $gap, PREG_OFFSET_CAPTURE, $separator)) {
                $lines[$i] = substr($lines[$i], 0, $gap[0][1])."\n".substr($lines[$i], $gap[0][1] + strlen($gap[0][0]));
            }

            break;
        }

        return implode("\n", $lines);
    }
}
