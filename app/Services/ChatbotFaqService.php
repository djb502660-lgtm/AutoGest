<?php

namespace App\Services;

use App\Models\ChatbotFaq;

class ChatbotFaqService
{
    /** @var list<string> */
    private const STOPWORDS = [
        'cual', 'cuales', 'como', 'que', 'para', 'del', 'una', 'unos', 'unas',
        'los', 'las', 'el', 'la', 'es', 'de', 'mi', 'tu', 'con', 'por', 'un',
        'al', 'en', 'se', 'su', 'sus', 'yo', 'me', 'te',
    ];

    public function answerForIntent(string $intent): ?string
    {
        $needles = match ($intent) {
            'hours' => ['horario', 'atencion', 'abren'],
            'services' => ['servicios', 'preventivo', 'correctivo'],
            default => [],
        };

        if ($needles === []) {
            return null;
        }

        $faq = ChatbotFaq::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->first(function (ChatbotFaq $faq) use ($needles) {
                $haystack = $this->normalize($faq->keywords.' '.$faq->question);

                foreach ($needles as $needle) {
                    if (str_contains($haystack, $needle)) {
                        return true;
                    }
                }

                return false;
            });

        return $faq?->answer;
    }

    public function answerFor(string $normalized): ?string
    {
        return $this->bestMatch($normalized)?->answer;
    }

    public function bestMatch(string $normalized): ?ChatbotFaq
    {
        $best = null;
        $bestScore = 0;

        foreach (ChatbotFaq::query()->where('is_active', true)->orderBy('sort_order')->get() as $faq) {
            $score = $this->score($normalized, $faq);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $faq;
            }
        }

        return $bestScore >= 3 ? $best : null;
    }

    private function score(string $normalized, ChatbotFaq $faq): int
    {
        $score = 0;
        $keywords = preg_split('/[,;]+/', (string) $faq->keywords, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($keywords as $keyword) {
            $keyword = $this->normalize($keyword);
            if (mb_strlen($keyword) < 3) {
                continue;
            }
            if (str_contains($normalized, $keyword)) {
                $score += 3;
            }
        }

        $question = $this->normalize($faq->question);
        foreach (preg_split('/\s+/u', $question, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $token) {
            if (mb_strlen($token) < 4 || in_array($token, self::STOPWORDS, true)) {
                continue;
            }
            if (str_contains($normalized, $token)) {
                $score += 1;
            }
        }

        return $score;
    }

    private function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text), 'UTF-8');
        $map = [
            '/[áàâãä]/u' => 'a', '/[éèêë]/u' => 'e',
            '/[íìîï]/u' => 'i', '/[óòôõö]/u' => 'o',
            '/[úùûü]/u' => 'u', '/[ñ]/u' => 'n',
        ];

        $text = preg_replace(array_keys($map), array_values($map), $text) ?? $text;

        return trim(preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text) ?? $text);
    }
}
