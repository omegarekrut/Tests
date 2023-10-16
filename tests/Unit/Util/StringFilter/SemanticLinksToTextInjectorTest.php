<?php

namespace Tests\Unit\Util\StringFilter;

use App\Domain\Record\Article\Entity\Article;
use App\Domain\Record\Common\Collection\RecordSemanticLinkCollection;
use App\Domain\Record\Common\Entity\RecordSemanticLink;
use App\Domain\SemanticLink\Entity\SemanticLink;
use App\Util\StringFilter\SemanticLinksToTextInjector;
use Generator;
use Ramsey\Uuid\Uuid;
use Tests\Unit\TestCase;

class SemanticLinksToTextInjectorTest extends TestCase
{
    /**
     * @param string[][] $recordSemanticLinksData
     *
     * @dataProvider getTextWithSemanticLinksInSource
     */
    public function testIntegrationSemanticLink(string $text, array $recordSemanticLinksData, string $expectedText): void
    {
        $recordSemanticLinkCollection = new RecordSemanticLinkCollection(
            array_map(function ($recordSemanticLinkData) {
                return $this->createRecordSemanticLink(...$recordSemanticLinkData);
            }, $recordSemanticLinksData)
        );

        $semanticLinksToTextInjector = new SemanticLinksToTextInjector();
        $actualText = $semanticLinksToTextInjector($text, $recordSemanticLinkCollection);

        $this->assertEquals($expectedText, $actualText);
    }

    /**
     * @phpcsSuppress Generic.Files.LineLength
     */
    public function getTextWithSemanticLinksInSource(): Generator
    {
        $articleSemanticLinkData = [
            'видео про рыбалку',
            '/articles/1984',
            'видео про рыбалку',
        ];

        $partnerSemanticLinkData = [
            'daiwa или shimano',
            'https://partner.ru/product/915',
            null,
        ];

        yield [
            'Lorem ipsum dolor видео про рыбалку sit amet, consectetur adipiscing elit. Катушки фирм daiwa или shimano признаны лучшими в своей категории. А также есть про рыбалку видео!',
            [
                'articleSemanticLink' => $articleSemanticLinkData,
                'partnerSemanticLink' => $partnerSemanticLinkData,
            ],
            'Lorem ipsum dolor <a href="/articles/1984">видео про рыбалку</a> sit amet, consectetur adipiscing elit. Катушки фирм <a href="https://partner.ru/product/915">daiwa или shimano</a> признаны лучшими в своей категории. А также есть про рыбалку видео!',
        ];

        yield [
            'Lorem ipsum dolor видео про рыбалку sit amet, consectetur adipiscing elit. Катушки фирм daiwa или shimano признаны лучшими в своей категории.',
            [
                'partnerSemanticLink' => $partnerSemanticLinkData,
            ],
            'Lorem ipsum dolor видео про рыбалку sit amet, consectetur adipiscing elit. Катушки фирм <a href="https://partner.ru/product/915">daiwa или shimano</a> признаны лучшими в своей категории.',
        ];

        yield [
            'Lorem ipsum dolor видео про рыбалку sit amet, consectetur adipiscing elit. Катушки фирм daiwa или shimano признаны лучшими в своей категории.',
            [
                'articleSemanticLink' => $articleSemanticLinkData,
            ],
            'Lorem ipsum dolor <a href="/articles/1984">видео про рыбалку</a> sit amet, consectetur adipiscing elit. Катушки фирм daiwa или shimano признаны лучшими в своей категории.',
        ];

        yield [
            'Lorem ipsum dolor видео про рыбалку sit amet, consectetur adipiscing elit. Катушки фирм daiwa или shimano признаны лучшими в своей категории.',
            [],
            'Lorem ipsum dolor видео про рыбалку sit amet, consectetur adipiscing elit. Катушки фирм daiwa или shimano признаны лучшими в своей категории.',
        ];

        yield [
            'Lorem ipsum dolor видео про рыбалку sit amet, consectetur adipiscing elit. Катушки фирм <a href="https://partner.ru/product/910">daiwa или shimano</a> признаны лучшими в своей категории.',
            [
                'articleSemanticLink' => $articleSemanticLinkData,
                'partnerSemanticLink' => $partnerSemanticLinkData,
            ],
            'Lorem ipsum dolor <a href="/articles/1984">видео про рыбалку</a> sit amet, consectetur adipiscing elit. Катушки фирм <a href="https://partner.ru/product/910">daiwa или shimano</a> признаны лучшими в своей категории.',
        ];

        yield [
            'Lorem ipsum dolor <a href="/articles/154">видео про рыбалку</a> sit amet, consectetur adipiscing elit. Катушки фирм daiwa или shimano признаны лучшими в своей категории.',
            [
                'articleSemanticLink' => $articleSemanticLinkData,
                'partnerSemanticLink' => $partnerSemanticLinkData,
            ],
            'Lorem ipsum dolor <a href="/articles/154">видео про рыбалку</a> sit amet, consectetur adipiscing elit. Катушки фирм <a href="https://partner.ru/product/915">daiwa или shimano</a> признаны лучшими в своей категории.',
            ];

        yield [
            'Lorem ipsum dolor <a href="/articles/154">видео про рыбалку</a> sit amet, consectetur adipiscing elit. Катушки фирм <a href="https://partner.ru/product/910">daiwa или shimano</a> признаны лучшими в своей категории.',
            [
                'articleSemanticLink' => $articleSemanticLinkData,
                'partnerSemanticLink' => $partnerSemanticLinkData,
            ],
            'Lorem ipsum dolor <a href="/articles/154">видео про рыбалку</a> sit amet, consectetur adipiscing elit. Катушки фирм <a href="https://partner.ru/product/910">daiwa или shimano</a> признаны лучшими в своей категории.',
        ];
    }

    private function createRecordSemanticLink(string $text, string $uri, ?string $combinationWords = null): RecordSemanticLink
    {
        $article = $this->createMock(Article::class);

        $semanticLink = $this->createMock(SemanticLink::class);
        $semanticLink
            ->method('getText')
            ->willReturn($text);

        $semanticLink
            ->method('getUri')
            ->willReturn($uri);

        return new RecordSemanticLink(Uuid::uuid4(), $article, $semanticLink, $combinationWords ?? $text);
    }
}
