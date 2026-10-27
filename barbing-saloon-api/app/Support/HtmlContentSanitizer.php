<?php

declare(strict_types=1);

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlContentSanitizer
{
    public function sanitize(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,br,strong,b,em,i,u,s,strike,del,blockquote,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,a[href|title|target|rel],img[src|alt|title|width|height],hr');
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);

        return (new HTMLPurifier($config))->purify($html);
    }
}
