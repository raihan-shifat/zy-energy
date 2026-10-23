<?php

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

if (! function_exists('safe_html')) {
    /**
     * Rich-text HTML from the editors (CKEditor / Summernote), cleaned for output:
     * formatting, headings, lists, tables, figures, links and images are kept with
     * their class/style; scripts, event handlers, iframes and javascript: URLs are removed.
     * Stored content is not changed. Use as {!! safe_html($html) !!}.
     */
    function safe_html(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        static $sanitizer = null;
        $sanitizer ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                ->allowSafeElements()
                ->allowAttribute('class', '*')
                ->allowAttribute('style', '*')
                ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
                ->allowRelativeLinks()
                ->allowMediaSchemes(['http', 'https'])
                ->allowRelativeMedias()
                ->withMaxInputLength(-1)
        );

        return $sanitizer->sanitize($html);
    }
}
