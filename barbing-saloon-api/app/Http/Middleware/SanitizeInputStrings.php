<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\InputSanitizer;
use Closure;
use Illuminate\Foundation\Http\Middleware\TransformsRequest;
use Illuminate\Http\Request;

class SanitizeInputStrings extends TransformsRequest
{
    /**
     * Attributes that should NOT be sanitized (e.g. passwords and cryptographic keys).
     *
     * @var array<int, string>
     */
    protected array $except = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'api_key',
        'secret',
        'signature',
        'refresh_token',
        'verification_code',
    ];

    /**
     * Rich-text attributes that permit safe HTML formatting (sanitized via HTMLPurifier).
     *
     * @var array<int, string>
     */
    protected array $richTextAttributes = [
        'body',
        'content',
        'html_content',
        'rich_text',
        'page_content',
        'article',
    ];

    public function handle($request, Closure $next)
    {
        // Skip webhook payloads which require raw verification
        if ($request->is('*/payments/webhook') || $request->is('payments/webhook')) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }

    /**
     * Transform the given value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transform($key, $value)
    {
        if (! is_string($value)) {
            return $value;
        }

        // Get leaf attribute name (e.g., 'customer.name' -> 'name')
        $leafKey = str_contains($key, '.') ? substr(strrchr($key, '.'), 1) : $key;

        // Skip exempted sensitive attributes
        if (in_array($leafKey, $this->except, true)) {
            return $value;
        }

        // Rich-text fields get purified using HTMLPurifier
        if (in_array($leafKey, $this->richTextAttributes, true)) {
            return InputSanitizer::cleanRichText($value);
        }

        // Plain-text fields get completely stripped of all HTML tags and control characters
        return InputSanitizer::cleanPlainText($value);
    }
}
