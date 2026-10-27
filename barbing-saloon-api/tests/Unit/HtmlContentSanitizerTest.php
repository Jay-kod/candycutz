<?php

use App\Support\HtmlContentSanitizer;

it('removes executable markup while preserving safe blog formatting', function () {
    $sanitized = (new HtmlContentSanitizer)->sanitize(
        '<p onclick="alert(1)">Safe <strong>formatting</strong><script>alert(2)</script></p>'
        .'<a href="javascript:alert(3)">link</a><a href="https://example.com">safe link</a>'
        .'<img src="https://example.com/image.jpg" onerror="alert(4)">'
    );

    expect($sanitized)->toContain('<p>');
    expect($sanitized)->toContain('<strong>formatting</strong>');
    expect($sanitized)->toContain('href="https://example.com"');
    expect($sanitized)->toContain('https://example.com/image.jpg');
    expect($sanitized)->not->toContain('onclick');
    expect($sanitized)->not->toContain('onerror');
    expect($sanitized)->not->toContain('javascript:');
    expect($sanitized)->not->toContain('<script>');
});
