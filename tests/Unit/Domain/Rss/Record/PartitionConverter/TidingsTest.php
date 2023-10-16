<?php

namespace Tests\Unit\Domain\Rss\Record\PartitionConverter;

use App\Domain\Record\Tidings\Entity\Tidings;
use App\Domain\Record\Tidings\ValueObject\FishingMethodCollection;
use App\Domain\Rss\Record\PartitionConverter\Tidings as TidingsConverter;
use App\Util\ImageStorage\Collection\ImageCollection;
use App\Util\ImageStorage\Image;
use Carbon\Carbon;

/**
 * @group rss
 */
class TidingsTest extends TestCase
{
    private const FISHING_DIARY_LABELS = [
        'Дата рыбалки',
        'Время ловли',
        'Место ловли',
        'Способ ловли',
        'Снасти, насадки, прикормки',
        'Улов',
        'Погода',
    ];

    /** @var TidingsConverter */
    private $tidingsConverter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tidingsConverter = new TidingsConverter(
            $this->createContentEncodedCollectionFactoryMock(),
            $this->createCollectionBuilderMock(),
            $this->createEnclosureChooserMock(),
            $this->createCategoryChooserMock(),
            $this->createUrlGeneratorMock()
        );
    }

    protected function tearDown(): void
    {
        unset($this->tidingsConverter);

        parent::tearDown();
    }

    public function testContentMustContainsTidingsText(): void
    {
        /** @var Tidings $tidings */
        $tidings = $this->createConfiguredMock(Tidings::class, [
            'getText' => 'Tidings text',
            'getImages' => new ImageCollection(),
        ]);

        $this->assertStringContainsString($tidings->getText(), $this->tidingsConverter->convertContent($tidings));
    }

    public function testContentMustContainsTidingsFishingDiary(): void
    {
        /** @var Tidings $tidings */
        $tidings = $this->createConfiguredMock(Tidings::class, [
            'getDateStart' => Carbon::now(),
            'getDateEnd' => Carbon::tomorrow(),
            'getFishingTime' => 'fishing time',
            'getPlace' => 'place',
            'getFishingMethods' => new FishingMethodCollection(FishingMethodCollection::FISHING_METHODS),
            'getTackles' => 'tackles',
            'getCatch' => 'catch',
            'getWeather' => 'weather',
            'getImages' => new ImageCollection(),
        ]);

        $content = $this->tidingsConverter->convertContent($tidings);

        $this->assertStringContainsString($tidings->getDateStart()->format('d.m.Y'), $content);
        $this->assertStringContainsString($tidings->getDateEnd()->format('d.m.Y'), $content);
        $this->assertStringContainsString($tidings->getFishingTime(), $content);
        $this->assertStringContainsString($tidings->getPlace(), $content);
        $this->assertStringContainsString((string) $tidings->getFishingMethods(), $content);
        $this->assertStringContainsString($tidings->getTackles(), $content);
        $this->assertStringContainsString($tidings->getCatch(), $content);
        $this->assertStringContainsString($tidings->getWeather(), $content);

        foreach (self::FISHING_DIARY_LABELS as $diaryLabel) {
            $this->assertStringContainsString($diaryLabel, $content);
        }
    }

    public function testContentMustNotContainsDiaryLabelsWithoutData(): void
    {
        /** @var Tidings $tidings */
        $tidings = $this->createConfiguredMock(Tidings::class, [
            'getImages' => new ImageCollection(),
        ]);

        $content = $this->tidingsConverter->convertContent($tidings);

        foreach (self::FISHING_DIARY_LABELS as $diaryLabel) {
            $this->assertStringNotContainsString($diaryLabel, $content);
        }
    }

    public function testContentMustContainsTidingsImages(): void
    {
        $expectedImage = new Image('image.jpeg');

        /** @var Tidings $tidings */
        $tidings = $this->createConfiguredMock(Tidings::class, [
            'getImages' => new ImageCollection([$expectedImage]),
        ]);

        $this->assertStringContainsString($expectedImage->getFilename(), $this->tidingsConverter->convertContent($tidings));
    }

    public function testTidingsTextMustBeConvertedAsItemDescription(): void
    {
        /** @var Tidings $tidings */
        $tidings = $this->createConfiguredMock(Tidings::class, [
            'getText' => 'Tidings text',
            'getImages' => new ImageCollection(),
        ]);

        $this->assertStringContainsString($tidings->getText(), $this->tidingsConverter->convertDescription($tidings));
    }
}
