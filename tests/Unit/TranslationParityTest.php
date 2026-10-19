<?php

namespace Tests\Unit;

use App\Console\Commands\LangCheck;
use Tests\TestCase;

class TranslationParityTest extends TestCase
{
    /**
     * Every key in resources/lang/en must exist in resources/lang/zh.
     * Without this, LocaleMiddleware's English fallback hides the gap on /zh.
     */
    public function test_zh_has_every_english_translation_key(): void
    {
        $missing = LangCheck::missingKeys('zh');

        $lines = [];
        foreach ($missing as $file => $keys) {
            foreach ($keys as $key) {
                $lines[] = "{$file}.php: {$key}";
            }
        }

        $this->assertSame([], $lines, "Keys missing in resources/lang/zh:\n".implode("\n", $lines));
    }
}
