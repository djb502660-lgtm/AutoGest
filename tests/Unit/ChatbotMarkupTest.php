<?php

namespace Tests\Unit;

use App\Support\ChatbotMarkup;
use PHPUnit\Framework\TestCase;

class ChatbotMarkupTest extends TestCase
{
    public function test_it_renders_bold_and_escapes_html(): void
    {
        $html = ChatbotMarkup::toHtml("Soy **AutoGest** <script>alert(1)</script>");

        $this->assertStringContainsString('<strong>AutoGest</strong>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
    }

    public function test_it_preserves_line_breaks(): void
    {
        $html = ChatbotMarkup::toHtml("Línea uno\nLínea dos");

        $this->assertStringContainsString("Línea uno<br>\nLínea dos", $html);
    }
}
