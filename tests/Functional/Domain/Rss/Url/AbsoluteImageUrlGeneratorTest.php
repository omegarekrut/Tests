<?php

namespace Tests\Functional\Domain\Rss\Url;

use App\Domain\Rss\Url\AbsoluteImageUrlGenerator;
use App\Domain\Rss\Url\AbsoluteImageUrlGeneratorInterface;
use App\Util\ImageStorage\Image;
use Tests\Functional\TestCase;

class AbsoluteImageUrlGeneratorTest extends TestCase
{
    /** @var AbsoluteImageUrlGeneratorInterface  */
    private $absoluteImageUrlGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->absoluteImageUrlGenerator = $this->getContainer()->get(AbsoluteImageUrlGenerator::class);
    }

    public function testGeneratorMustGenerateUrlWithExpectedSizes(): void
    {
        $image = new Image('image.jpg');

        $absoluteUrl = $this->absoluteImageUrlGenerator->createAbsoluteImageUrl($image);

        $this->assertUrlIsAbsolute($absoluteUrl);
    }

    private function assertUrlIsAbsolute(string $absoluteUrl): void
    {
        $isAbsoluteUrl = (bool) filter_var($absoluteUrl, FILTER_VALIDATE_URL);

        $this->assertTrue($isAbsoluteUrl);
    }
}
