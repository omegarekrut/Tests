<?php

namespace Tests\Functional\Module\YandexZen\ContentPuller;

use App\Module\YandexZen\ContentPuller\ContentEncodedCollectionFactory;
use Tests\Functional\TestCase;
use ZenRss\ContentPuller\ContentType\Image;

/**
 * @group yandex-zen
 */
class ContentEncodedCollectionFactoryTest extends TestCase
{
    /** @var ContentEncodedCollectionFactory */
    private $contentEncodedCollectionFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contentEncodedCollectionFactory = $this->getContainer()->get(ContentEncodedCollectionFactory::class);
    }

    protected function tearDown(): void
    {
        unset($this->contentEncodedCollectionFactory);

        parent::tearDown();
    }

    public function testFactoryCanCreateReconfiguredCollection(): void
    {
        $expectedCopyright = 'expected copyright';
        $expectedTitle = 'expected title';

        $sourceContent = '<img src="http://foo.bar/image" />';
        $contentCollection = $this->contentEncodedCollectionFactory->create($sourceContent, $expectedCopyright, $expectedTitle);

        $image = current($contentCollection->getContentByType(Image::class));
        assert($image instanceof Image);

        $this->assertEquals($expectedCopyright, $image->getCopyright());
        $this->assertEquals($expectedTitle, $image->getTitle());
    }
}
