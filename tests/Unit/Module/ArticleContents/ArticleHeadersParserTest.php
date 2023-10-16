<?php

namespace Tests\Unit\Module\ArticleContents;

use App\Module\ArticleContents\ArticleHeadersParser;
use Tests\Unit\TestCase;

class ArticleHeadersParserTest extends TestCase
{
    /**
     * @dataProvider getCases
     */
    public function testParseHeaders(string $article, array $expectedContents): void
    {
        $parser = new ArticleHeadersParser();

        $this->assertEquals(
            $expectedContents,
            $parser->parseHeaders($article)
        );
    }

    public function getCases(): array
    {
        return [
            'without headers' => [
                'some article text about fishing',
                [],
            ],
            'few headers' => [
                '<h2>some</h2> article text <h2>about</h2> fishing',
                [
                    [
                        'level' => 2,
                        'header' => 'some',
                        'id' => ''
                    ],
                    [
                        'level' => 2,
                        'header' => 'about',
                        'id' => ''
                    ],
                ],
            ],
            'excess h1 h5 with normal headers' => [
                '<h1>header</h1><h2>some</h2> article text <h2>about</h2> fishing at <h3>lakes</h3> and <h4>rivers</h4> or <h5>seas</h5>',
                [
                    [
                        'level' => 2,
                        'header' => 'some',
                        'id' => ''
                    ],
                    [
                        'level' => 2,
                        'header' => 'about',
                        'id' => ''
                    ],
                    [
                        'level' => 3,
                        'header' => 'lakes',
                        'id' => ''
                    ],
                    [
                        'level' => 4,
                        'header' => 'rivers',
                        'id' => ''
                    ],
                ],
            ],
            'normal contents generation with h2 h3 h4' => [
                '<h2>some</h2> <h3>same</h3> article text <h3>about</h3> <h4>siberian</h4> fishing <h4 id="and-id">and</h4> about <h2>fishing</h2> gear',
                [
                    [
                        'level' => 2,
                        'header' => 'some',
                        'id' => ''
                    ],
                    [
                        'level' => 3,
                        'header' => 'same',
                        'id' => ''
                    ],
                    [
                        'level' => 3,
                        'header' => 'about',
                        'id' => ''
                    ],
                    [
                        'level' => 4,
                        'header' => 'siberian',
                        'id' => ''
                    ],
                    [
                        'level' => 4,
                        'header' => 'and',
                        'id' => 'and-id'
                    ],
                    [
                        'level' => 2,
                        'header' => 'fishing',
                        'id' => ''
                    ],
                ],
            ],
        ];
    }
}
