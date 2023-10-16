<?php

namespace Tests\Unit\Twig\Hashtag;

use App\Domain\Hashtag\Collection\HashtagCollection;
use App\Domain\Hashtag\Entity\Hashtag;
use App\Domain\Hashtag\Parser\HashtagParser;
use App\Domain\Hashtag\Repository\HashtagRepository;
use App\Twig\Hashtag\HashtagLinkerFilter;
use Tests\Unit\TestCase;

/**
 * @group twig
 */
class HashtagLinkerFilterTest extends TestCase
{
    private const HASHTAG_NAME = 'рыбка';

    private $hashtagLinkerFitler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hashtagLinkerFitler = new HashtagLinkerFilter(
            $this->getHashtagRepository(),
            new HashtagParser()
        );
    }

    private function getHashtagRepository(): HashtagRepository
    {
        $hashtagStub = $this->createMock(Hashtag::class);
        $hashtagStub
            ->method('getName')
            ->willReturn(self::HASHTAG_NAME);
        $hashtagStub
            ->method('getSlug')
            ->willReturn('fish');

        $repositoryStub = $this->createMock(HashtagRepository::class);
        $repositoryStub->method('findByNames')
            ->willReturn(new HashtagCollection([$hashtagStub]));

        return $repositoryStub;
    }

    public function testFilterOnTextWithoutTags(): void
    {
        $text = 'some text';

        $this->assertEquals($text, ($this->hashtagLinkerFitler)($text));
    }

    public function testFilterOnTextWithTags(): void
    {
        $text = 'some text #рыбка text after tag';

        $this->assertEquals('some text <a href="/hashtag/fish/">#рыбка</a> text after tag', ($this->hashtagLinkerFitler)($text));
    }
}
