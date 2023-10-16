<?php

namespace Tests\Unit\Domain\Record\Common\View;

use App\Domain\Record\Common\View\VideoUrlViewFactory;
use App\Module\VideoInformationLoader\EmbedVideoInformationLoader;
use App\Module\VideoInformationLoader\VideoInformation;
use Tests\Unit\TestCase;

class VideoUrlViewFactoryPreviewImageTest extends TestCase
{
    private const IMAGE_URL = 'www.image.ru/image';
    private const VIDEO_URL = 'www.video.ru/video';

    public function testVideoInformationLoaderReturnNull(): void
    {
        $videoUrlViewFactory = $this->getVideoUrlViewFactory(null);
        $this->assertNotNull($videoUrlViewFactory->create(self::VIDEO_URL)->videoPreviewImagePath);
    }

    public function testVideoInformationLoaderReturnImageUrl(): void
    {
        $videoUrlViewFactory = $this->getVideoUrlViewFactory(self::IMAGE_URL);
        $this->assertEquals($videoUrlViewFactory->create(self::VIDEO_URL)->videoPreviewImagePath, self::IMAGE_URL);
    }

    private function getVideoUrlViewFactory(?string $url): VideoUrlViewFactory
    {
        $videoInformation = new VideoInformation('', null, $url);
        $embedVideoInformationLoader = $this->createMock(EmbedVideoInformationLoader::class);
        $embedVideoInformationLoader->method('loadInformation')->willReturn($videoInformation);

        return new VideoUrlViewFactory($embedVideoInformationLoader);
    }
}
