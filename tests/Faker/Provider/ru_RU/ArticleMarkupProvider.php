<?php

namespace Tests\Faker\Provider\ru_RU;

class ArticleMarkupProvider extends MarkupProvider
{
    public function randomBBCodeWithMoreHeaders(int $maxNbChars = 200): string
    {
        $text = $this->realText($maxNbChars);
        $words = self::explode($text);

        $words = $this->decorateWords($words, 5, '[h2]%s[/h2]');
        $words = $this->decorateWords($words, 10, '[h3]%s[/h3]');
        $words = $this->decorateWords($words, 10, '[i]%s[/i]');
        $words = $this->decorateWords($words, 10, '[b]%s[/b]');
        $words = $this->decorateWords($words, 10, '[u]%s[/u]');
        $words = $this->decorateWords($words, 10, '[s]%s[/s]');

        return self::implode($words);
    }
}
