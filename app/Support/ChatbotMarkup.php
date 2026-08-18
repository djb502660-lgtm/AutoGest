<?php

namespace App\Support;

class ChatbotMarkup
{
    public static function toHtml(string $text): string
    {
        $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $withBold = preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $escaped) ?? $escaped;

        return nl2br($withBold, false);
    }
}
