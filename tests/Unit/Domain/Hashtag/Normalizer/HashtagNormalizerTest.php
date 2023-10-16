<?php

namespace Tests\Unit\Domain\Hashtag\Normalizer;

use App\Domain\Hashtag\Entity\Hashtag;
use App\Domain\Hashtag\Normalizer\HashtagNormalizer;
use Tests\Unit\TestCase;

/**
 * @group hashtags
 */
class HashtagNormalizerTest extends TestCase
{
    /** @var HashtagNormalizer */
    private $hashtagNormalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hashtagNormalizer = new HashtagNormalizer();
    }

    public function testNormalizeWithEmptyArray()
    {
        $hashtags = [];

        $normalizedHashtags = $this->hashtagNormalizer->normalize($hashtags);

        $expectedNormalizedData = [
            'hashtags' => []
        ];

        $this->assertEquals($expectedNormalizedData, $normalizedHashtags);
    }

    public function testNormalize()
    {
        $hashtags = [$this->getMockHashtag('hashtag')];

        $normalizedBranches = $this->hashtagNormalizer->normalize($hashtags);

        $expectedNormalizedData = [
            'hashtags' => ['hashtag']
        ];

        $this->assertEquals($expectedNormalizedData, $normalizedBranches);
    }

    private function getMockHashtag(string $name): Hashtag
    {
        $mock = $this->createMock(Hashtag::class);

        $mock
            ->method('getName')
            ->willReturn($name);

        return $mock;
    }
}
