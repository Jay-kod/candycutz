<?php

declare(strict_types=1);

namespace App\Support;

class InputSanitizer
{
    /**
     * Clean and strip all markup and invisible control characters from a plain-text input string.
     */
    public static function cleanPlainText(?string $input, int $maxLength = 0): string
    {
        if ($input === null || $input === '') {
            return '';
        }

        // 1. Remove null-bytes
        $cleaned = str_replace(chr(0), '', $input);

        // 2. Remove executable blocks completely including their content (script, style, svg, iframe, noscript)
        $cleaned = (string) preg_replace('/<(script|style|svg|iframe|noscript|object|embed)[^>]*>.*?<\/\1>/is', '', $cleaned);
        $cleaned = (string) preg_replace('/<(script|style|svg|iframe|noscript|object|embed)[^>]*\/?>/is', '', $cleaned);

        // 3. Strip all remaining HTML and XML tags
        $cleaned = strip_tags($cleaned);

        // 4. Remove dangerous pseudo-protocols
        $cleaned = (string) preg_replace('/(javascript|vbscript|data):/i', '', $cleaned);

        // 5. Strip invisible control characters (preserve normal newlines and tabs)
        $cleaned = (string) preg_replace('/[^\P{C}\n\r\t]/u', '', $cleaned);

        $cleaned = trim($cleaned);

        if ($maxLength > 0 && mb_strlen($cleaned) > $maxLength) {
            $cleaned = mb_substr($cleaned, 0, $maxLength);
        }

        return $cleaned;
    }

    /**
     * Clean rich-text HTML input using HTMLPurifier.
     */
    public static function cleanRichText(?string $html): string
    {
        return app(HtmlContentSanitizer::class)->sanitize($html);
    }
}
