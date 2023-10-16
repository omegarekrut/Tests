<?php

namespace Tests\Functional\Twig\Comment;

use App\Twig\Comment\CommentTextViewFilter;
use Tests\Functional\TestCase as FunctionalTestCase;

/**
 * @group twig
 */
class CommentTextViewFilterTest extends FunctionalTestCase
{
    /**
     * @dataProvider commentData
     */
    public function testFilter(string $sourceText, string $expectedText): void
    {
        $commentTextViewFilter = $this->getContainer()->get(CommentTextViewFilter::class);

        $this->assertEquals($expectedText, $commentTextViewFilter($sourceText));
    }

    public function commentData(): \Generator
    {
        yield [
            ' some text ',
            'some text',
        ];

        yield [
            "some\ntext",
            "some<br>\ntext",
        ];

        yield [
            'A \'quote\' is <b>bold</b>',
            'A \'quote\' is &lt;b&gt;bold&lt;/b&gt;',
        ];

        yield [
            'some text https://ya.ru text after link',
            'some text <a href="https://ya.ru" target="_blank" rel="nofollow">https://ya.ru</a> text after link',
        ];
    }
}
