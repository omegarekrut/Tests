<?php

namespace Tests\Unit\Domain\Rss\Record\Repository;

use App\Domain\Rss\Record\Repository\ValidImageChooser;
use FastImageSize\FastImageSize;
use Tests\Unit\TestCase;
use ZenRss\Entity\Enclosure;
use ZenRss\Entity\ItemInterface;

/**
 * @group rss
 */
class ValidImageChooserTest extends TestCase
{
    /** @var ValidImageChooser */
    private $validImageChooser;
    private $notImageUrl;
    private $nonexistentImageUrl;
    private $smallImageUrl;
    private $optimalImageUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validImageChooser = new ValidImageChooser(new FastImageSize(), 40, 57);
        $this->notImageUrl = $this->getDataFixturesFolder().'tempFile.txt';
        $this->nonexistentImageUrl = $this->getDataFixturesFolder().'imageNotExists.jpeg';
        $this->smallImageUrl = $this->getDataFixturesFolder().'image20x29.jpeg';
        $this->optimalImageUrl = $this->getDataFixturesFolder().'image40x57.jpeg';
    }

    public function testEmptyEnclosures(): void
    {
        $item = $this->createItemWithEnclosures([]);

        $this->assertEmpty(call_user_func($this->validImageChooser, $item));
    }

    public function testItemWithoutImages(): void
    {
        $item = $this->createItemWithEnclosures([
            new Enclosure('video/mimetype', 'http://foo.bar'),
        ]);

        $this->assertEmpty(call_user_func($this->validImageChooser, $item));
    }

    public function testItemWithNonexistentImage(): void
    {
        $item = $this->createItemWithEnclosures([
            new Enclosure('image/jpeg', $this->nonexistentImageUrl),
        ]);

        $this->assertEmpty(call_user_func($this->validImageChooser, $item));
    }

    public function testItemWithInvalidFormatImage(): void
    {
        $item = $this->createItemWithEnclosures([
            new Enclosure('image/jpeg', $this->notImageUrl),
        ]);

        $this->assertEmpty(call_user_func($this->validImageChooser, $item));
    }

    public function testItemWithSmallImage(): void
    {
        $item = $this->createItemWithEnclosures([
            new Enclosure('image/jpeg', $this->smallImageUrl),
        ]);

        $this->assertEmpty(call_user_func($this->validImageChooser, $item));
    }

    public function testItemWithOptimalImage(): void
    {
        $enclosure = new Enclosure('image/jpeg', $this->optimalImageUrl);
        $item = $this->createItemWithEnclosures([$enclosure]);

        $this->assertEquals($enclosure, call_user_func($this->validImageChooser, $item));
    }

    private function createItemWithEnclosures(array $enclosures): ItemInterface
    {
        $stub = $this->createMock(ItemInterface::class);
        $stub
            ->expects($this->any())
            ->method('getEnclosures')
            ->willReturn($enclosures);

        return $stub;
    }
}
