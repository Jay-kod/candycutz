<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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

    /**
     * Extract and validate a safe HTTPS Google Maps embed URL from either
     * an HTML iframe snippet or a direct Google Maps embed URL.
     */
    public static function cleanGoogleMapsEmbed(?string $input): string
    {
        if ($input === null) {
            return '';
        }

        $cleaned = trim(str_replace(chr(0), '', $input));
        if ($cleaned === '') {
            return '';
        }

        $candidate = $cleaned;
        if (preg_match('/<iframe\b[^>]*\bsrc\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $cleaned, $matches) === 1) {
            $candidate = html_entity_decode($matches[1] ?: ($matches[2] ?: ($matches[3] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $candidate = trim((string) preg_replace('/[^\P{C}]/u', '', $candidate));
        if ($candidate === '' || preg_match('/[<>"\']/', $candidate) === 1) {
            return '';
        }

        $parts = parse_url($candidate);
        if (! is_array($parts)) {
            return '';
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = (string) ($parts['path'] ?? '');
        $query = (string) ($parts['query'] ?? '');

        if ($scheme !== 'https') {
            return '';
        }

        $allowedHosts = ['www.google.com', 'maps.google.com', 'google.com'];
        if (! in_array($host, $allowedHosts, true)) {
            return '';
        }

        $isEmbedPath = str_starts_with($path, '/maps/embed');
        $isLegacyEmbedQuery = str_starts_with($path, '/maps')
            && (str_contains($query, 'output=embed') || str_contains($query, 'pb='));

        if (! $isEmbedPath && ! $isLegacyEmbedQuery) {
            return '';
        }

        return $candidate;
    }

    /**
     * Extract [latitude, longitude] coordinates from a Google Maps embed snippet, URL, or share link.
     * Prioritizes exact place pin parameters (!3d<lat>!4d<lng>) before viewport camera parameters (!2d<lng>!3d<lat>).
     *
     * @return array{0: float, 1: float}|null
     */
    public static function extractGoogleMapsCoordinates(?string $url): ?array
    {
        $loc = self::extractGoogleMapsLocation($url);

        return $loc !== null ? [$loc['lat'], $loc['lng']] : null;
    }

    /**
     * Extract coordinates along with whether they represent an exact place pin or a viewport camera center.
     *
     * @return array{lat: float, lng: float, precise: bool}|null
     */
    public static function extractGoogleMapsLocation(?string $url): ?array
    {
        $trimmed = trim((string) $url);
        if ($trimmed === '') {
            return null;
        }

        $patterns = [
            // Exact Google Maps place pin parameter: !3d<lat>!4d<lng>
            ['regex' => '/!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/', 'lat' => 1, 'lng' => 2, 'precise' => true],
            // Explicit query pin: ?q=<lat>,<lng> or &ll=<lat>,<lng> or &center=<lat>,<lng>
            ['regex' => '/[?&](?:q|ll|query|center)=(-?\d+(?:\.\d+)?)(?:%2C|,)(-?\d+(?:\.\d+)?)/i', 'lat' => 1, 'lng' => 2, 'precise' => true],
            // Google Maps embed pb viewport camera center parameter: !2d<lng>!3d<lat>
            ['regex' => '/!2d(-?\d+(?:\.\d+)?)!3d(-?\d+(?:\.\d+)?)/', 'lat' => 2, 'lng' => 1, 'precise' => false],
            // Standard Google Maps URL viewport camera center: /@<lat>,<lng>
            ['regex' => '/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/', 'lat' => 1, 'lng' => 2, 'precise' => false],
        ];

        foreach ($patterns as $entry) {
            if (preg_match($entry['regex'], $trimmed, $m) === 1) {
                $lat = round((float) $m[$entry['lat']], 6);
                $lng = round((float) $m[$entry['lng']], 6);
                if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                    return [
                        'lat' => $lat,
                        'lng' => $lng,
                        'precise' => $entry['precise'],
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Safely resolve Google Maps short share URLs (e.g. https://maps.app.goo.gl/...) to their canonical place URL.
     */
    public static function resolveGoogleMapsShareUrl(?string $url): string
    {
        $trimmed = trim((string) $url);
        if ($trimmed === '') {
            return '';
        }

        $parts = parse_url($trimmed);
        if (! is_array($parts)) {
            return $trimmed;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        if ($scheme !== 'https' || ! in_array($host, ['maps.app.goo.gl', 'goo.gl'], true)) {
            return $trimmed;
        }

        return Cache::remember(
            'google_maps.resolved.'.sha1($trimmed),
            86400,
            function () use ($trimmed): string {
                $current = $trimmed;
                $allowedHosts = ['maps.app.goo.gl', 'goo.gl', 'www.google.com', 'maps.google.com', 'google.com'];

                try {
                    for ($hop = 0; $hop < 4; $hop++) {
                        $resp = Http::timeout(3)
                            ->withOptions(['allow_redirects' => false])
                            ->head($current);

                        $location = trim((string) ($resp->header('Location') ?? ''));
                        if ($location === '') {
                            break;
                        }

                        $nextParts = parse_url($location);
                        if (! is_array($nextParts)) {
                            break;
                        }

                        $nextScheme = strtolower((string) ($nextParts['scheme'] ?? ''));
                        $nextHost = strtolower((string) ($nextParts['host'] ?? ''));
                        if ($nextScheme !== 'https' || ! in_array($nextHost, $allowedHosts, true)) {
                            break;
                        }

                        $current = $location;
                        if (self::extractGoogleMapsLocation($current) !== null) {
                            break;
                        }
                    }
                } catch (\Throwable) {
                    return $trimmed;
                }

                return $current;
            }
        );
    }

    /**
     * Resolve the authoritative shop pin from Contact Page map inputs, preferring exact place markers
     * from a share link/embed over viewport-shifted iframe camera coordinates.
     *
     * @return array{lat: float, lng: float, precise: bool}|null
     */
    public static function resolveAuthoritativeMapPin(?string $mapIframe, ?string $mapLink): ?array
    {
        $resolvedLink = self::resolveGoogleMapsShareUrl($mapLink);
        $linkLoc = self::extractGoogleMapsLocation($resolvedLink);
        $iframeLoc = self::extractGoogleMapsLocation($mapIframe);

        if ($linkLoc !== null && $linkLoc['precise']) {
            return $linkLoc;
        }

        if ($iframeLoc !== null && $iframeLoc['precise']) {
            return $iframeLoc;
        }

        return $linkLoc ?? $iframeLoc;
    }

    /**
     * Extract a clean human-readable place name from a Google Maps embed or place link (e.g. "Candycutz").
     */
    public static function extractGoogleMapsPlaceName(?string $mapIframe, ?string $mapLink = null): ?string
    {
        foreach ([$mapLink, $mapIframe] as $source) {
            $raw = trim((string) $source);
            if ($raw === '') {
                continue;
            }

            if (preg_match('/\/place\/([^\/@?]+)/i', $raw, $m) === 1 || preg_match('/!2s([^!&?]+)/', $raw, $m) === 1) {
                $decoded = trim(urldecode(str_replace('+', ' ', $m[1])));
                // Ignore Place IDs, bare coordinates, or trivial tokens
                if ($decoded !== '' && ! preg_match('/^-?\d+(?:\.\d+)?(?:\s*,\s*-?\d+(?:\.\d+)?)?$/', $decoded) && ! str_starts_with($decoded, '0x')) {
                    return mb_substr(self::cleanPlainText($decoded, 120), 0, 120);
                }
            }
        }

        return null;
    }
}
