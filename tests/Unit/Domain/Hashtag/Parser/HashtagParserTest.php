<?php

namespace Tests\Unit\Domain\Hashtag\Parser;

use App\Domain\Hashtag\Collection\HashtagCollection;
use App\Domain\Hashtag\Entity\Hashtag;
use App\Domain\Hashtag\Parser\HashtagParser;
use Tests\DataFixtures\ORM\LoadHashtags;
use Tests\Unit\TestCase;

class HashtagParserTest extends TestCase
{
    /** @var HashtagParser */
    private $hashtagParser;

    private const HASHTAG_NAME = LoadHashtags::HASHTAG_NAME_FISHING;
    private const HASHTAG_SLUG = LoadHashtags::HASHTAG_SLUG_FISHING;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hashtagParser = new HashtagParser();
    }

    public function testGetHashtagNames(): void
    {
        $textWithHashtag = $this->getFaker()->text().' #'.self::HASHTAG_NAME;
        $hashtagNames = $this->hashtagParser->getHashtagNames($textWithHashtag);

        $this->assertEquals($hashtagNames, [self::HASHTAG_NAME]);
    }

    /**
     * @dataProvider getIgnoredBbTags
     */
    public function testGetHashtagNamesFromTextWithIgnoredBBCodes(string $openingTag, string $closingTag): void
    {
        $textWithBbTag = $this->getFaker()->text().$openingTag.'Зимняя #'.self::HASHTAG_NAME.$closingTag;
        $hashtagNames = $this->hashtagParser->getHashtagNames($textWithBbTag);

        $this->assertEmpty($hashtagNames);
    }

    public function getIgnoredBbTags(): array
    {
        return [
            'url' => ['[url="https://www.fishingsib.test"]', '[/url]'],
            'img' => ['[img="https://www.fishingsib.test"]', '[/img]'],
        ];
    }

    public function testGetHashtagNamesFromTextWithConsiderBBCodes(): void
    {
        $textWithBbTag = $this->getFaker()->text().'[s][u][b][i]Зимняя #'.self::HASHTAG_NAME.'[/i][/b][/u][/s]';
        $hashtagNames = $this->hashtagParser->getHashtagNames($textWithBbTag);

        $this->assertNotEmpty($hashtagNames);
    }

    public function testGetHashtagNamesFromTextWithHtml(): void
    {
        $textWithBbTag = '<p>'.$this->getFaker()->text().'</p><p>#'.self::HASHTAG_NAME.'</p>';
        $hashtagNames = $this->hashtagParser->getHashtagNames($textWithBbTag);

        $this->assertNotEmpty($hashtagNames);
    }

    public function testGetHashtagNamesFromTextWithHashSymbolIntoBBCodes(): void
    {
        $textWithBbTag = $this->getFaker()->text().'[color="#AA1122"]Зимняя рыбалка[/color]';
        $hashtagNames = $this->hashtagParser->getHashtagNames($textWithBbTag);

        $this->assertEmpty($hashtagNames);
    }

    public function testGetHashtagNamesFromTextWithHashSymbolIntoLink(): void
    {
        $textWithBbTag = $this->getFaker()->text().' http://fishingsib.loc/tidings#comments';
        $hashtagNames = $this->hashtagParser->getHashtagNames($textWithBbTag);

        $this->assertEmpty($hashtagNames);
    }
    
    public function testAddLinksToHashtags(): void
    {
        $textWithHashtag = $this->getFaker()->text().' #'.self::HASHTAG_NAME;
        $hashtagStub = $this->createMock(Hashtag::class);
        $hashtagStub
            ->method('getSlug')
            ->willReturn(self::HASHTAG_SLUG);

        $linkedText = $this->hashtagParser->addLinksToHashtags($textWithHashtag, new HashtagCollection([$hashtagStub]));

        //todo replace '/hashtag/%s/' by $router->generate() when refactor
        $this->assertTrue((bool) stripos($linkedText, sprintf('/hashtag/%s/', self::HASHTAG_SLUG)));
    }

    private function getHashtag(): Hashtag
    {
        return new Hashtag(self::HASHTAG_NAME);
    }
}
