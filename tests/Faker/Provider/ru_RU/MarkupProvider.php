<?php

namespace Tests\Faker\Provider\ru_RU;

class MarkupProvider extends TextProvider
{
    public function randomBBCode(int $maxNbChars = 200): string
    {
        $text = $this->realText($maxNbChars);
        $words = self::explode($text);

        $words = $this->decorateWords($words, 1, '[h2]%s[/h2]');
        $words = $this->decorateWords($words, 2, '[h3]%s[/h3]');
        $words = $this->decorateWords($words, 10, '[i]%s[/i]');
        $words = $this->decorateWords($words, 10, '[b]%s[/b]');
        $words = $this->decorateWords($words, 10, '[u]%s[/u]');
        $words = $this->decorateWords($words, 10, '[s]%s[/s]');

        return self::implode($words);
    }

    public function randomHtml(int $maxNbChars = 200): string
    {
        $text = $this->realText($maxNbChars);
        $words = self::explode($text);

        $words = $this->decorateWords($words, 1, '<h2>%s</h2>');
        $words = $this->decorateWords($words, 2, '<h3>%s</h3>');
        $words = $this->decorateWords($words, 10, '<i>%s</i>');
        $words = $this->decorateWords($words, 10, '<b>%s</b>');
        $words = $this->decorateWords($words, 10, '<u>%s</u>');

        return self::implode($words);
    }

    protected function decorateWords(array $words, int $countWords, string $template): array
    {
        $wordsCount = count($words);
        $decorator = function (string &$word) use ($template) {
            $word = sprintf($template, $word);
        };

        while ($countWords > 0) {
            $countWords--;
            $decorator($words[random_int(0, $wordsCount - 1)]);
        }

        return $words;
    }
}
