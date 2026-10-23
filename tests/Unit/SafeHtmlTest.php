<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SafeHtmlTest extends TestCase
{
    public function test_editor_content_is_kept(): void
    {
        $html = '<h4>Specs</h4><figure class="table"><table><tbody><tr><td><strong>3kW</strong></td></tr></tbody></table></figure>'
            .'<p style="color:#f00">red</p><a href="https://zzyenergy.com/products">link</a>';

        $this->assertSame($html, safe_html($html));
        $this->assertSame('<img src="/storage/editor/a.jpg" alt="a" />', safe_html('<img src="/storage/editor/a.jpg" alt="a">'));
    }

    public function test_script_and_event_handlers_are_removed(): void
    {
        $out = safe_html('<script>alert(1)</script><img src=x onerror=alert(1)><a href="javascript:alert(1)">j</a><iframe src="https://evil.test"></iframe><p onclick="x()">p</p>');

        $this->assertStringNotContainsString('<script', $out);
        $this->assertStringNotContainsString('onerror', $out);
        $this->assertStringNotContainsString('javascript:', $out);
        $this->assertStringNotContainsString('<iframe', $out);
        $this->assertStringNotContainsString('onclick', $out);
        $this->assertStringContainsString('<p>p</p>', $out);
    }

    public function test_empty_input_gives_empty_string(): void
    {
        $this->assertSame('', safe_html(null));
        $this->assertSame('', safe_html(''));
    }
}
