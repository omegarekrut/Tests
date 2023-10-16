<?php

namespace Tests\Unit\Module\ArticleContents;

use App\Module\ArticleContents\ArticleHeadersPreparer;
use Tests\Unit\TestCase;

class ArticleHeadersPreparerTest extends TestCase
{
    /**
     * @dataProvider getCases
     */
    public function testAddContentsAnchorsToBody(string $articleHtml, string $expectedHtml): void
    {
        $preparer = new ArticleHeadersPreparer();

        $this->assertEquals(
            $expectedHtml,
            $preparer->addContentsAnchorsToBody($articleHtml)
        );
    }

    public function getCases(): array
    {
        return [
            'add some ids' => [
                '<p><h2>some</h2><h3 id="id2">same</h3><p>article text</p><h3>about</h3></p>',
                '<p><h2 id="article-contents-header-0">some</h2><h3 id="id2">same</h3><p>article text</p><h3 id="article-contents-header-2">about</h3></p>',
            ],
            'do nothing' => [
                '<p><h2 id="id1">some</h2><h3 id="id2">same</h3><p>article text</p><h3 id="id3">about</h3></p>',
                '<p><h2 id="id1">some</h2><h3 id="id2">same</h3><p>article text</p><h3 id="id3">about</h3></p>',
            ],
            'article contains other attributes' => [
                '<p><h2 class="some-class">some</h2>',
                '<p><h2 class="some-class" id="article-contents-header-0">some</h2>',
            ],
            'multiline definition' => [
                "<h2 \nclass=\"some-class\"\n>\nSome\n</h2>",
                "<h2 \nclass=\"some-class\"\n id=\"article-contents-header-0\">\nSome\n</h2>",
            ],
        ];
    }
}
