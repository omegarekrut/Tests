<?php

namespace Tests\Functional\Module\VideoInformationLoader;

use App\Module\VideoInformationLoader\EmbedVideoInformationLoader;
use App\Module\VideoInformationLoader\Exception\VideoInformationCouldNotBeLoaderException;
use Tests\Functional\TestCase as FunctionalTestCase;

/**
 * @group real-remote-service-test
 */
class EmbedVideoInformationLoaderTest extends FunctionalTestCase
{
    private $embedVideoInformationLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->embedVideoInformationLoader = $this->getContainer()->get(EmbedVideoInformationLoader::class);
    }

    protected function tearDown(): void
    {
        unset($this->embedVideoInformationLoader);

        parent::tearDown();
    }

    public function testSuccessfulUploadOfInformationForVideo(): void
    {
        $videoInformation = $this->embedVideoInformationLoader->loadInformation('https://youtu.be/sdsAzKtPvCI');

        $this->assertNotEmpty($videoInformation->getTitle());
        $this->assertContains('jpg', $videoInformation->getImageUrl());
        $this->assertContains('iframe', $videoInformation->getHtmlCode());
    }

    public function testUnsuccessfulUploadOfInformationForVideo(): void
    {
        $videoInformation = $this->embedVideoInformationLoader->loadInformation('https://youtu.be/Ulz34E0vcvOEfck');

        $this->assertNull($videoInformation->getImageUrl());
        $this->assertNull($videoInformation->getHtmlCode());
    }

    public function testInformationForInvalidUrlCannotBeLoaded(): void
    {
        $this->expectException(VideoInformationCouldNotBeLoaderException::class);

        $this->embedVideoInformationLoader->loadInformation('invalid url');
    }
}
