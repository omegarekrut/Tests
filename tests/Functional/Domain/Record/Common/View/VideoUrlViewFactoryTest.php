<?php

namespace Tests\Functional\Domain\Record\Common\View;

use App\Domain\Record\Common\View\VideoUrlViewFactory;
use Tests\Functional\TestCase;

/**
 * @group record-view
 */
class VideoUrlViewFactoryTest extends TestCase
{
    private VideoUrlViewFactory $videoUrlViewFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->videoUrlViewFactory = $this->getContainer()->get(VideoUrlViewFactory::class);
    }

    protected function tearDown(): void
    {
        unset($this->videoUrlViewFactory);

        parent::tearDown();
    }

    public function testVideoInformationShouldBeObtainedByUrl(): void
    {
        $videoView = $this->videoUrlViewFactory->create('//www.youtube.com/embed/FRFUeCRSNyw?rel=0&amp;enablejsapi=1');

        $this->assertStringContainsString('<iframe', $videoView->htmlIframe);
        $this->assertStringContainsString('src="https://www.youtube.com/embed/FRFUeCRSNyw', $videoView->htmlIframe);
        $this->assertStringContainsString('//i.ytimg.com/vi/FRFUeCRSNyw/maxresdefault.jpg', $videoView->videoPreviewImagePath);
    }

    public function testVideoInformationShouldNotBeObtainedByText(): void
    {
        $videoView = $this->videoUrlViewFactory->create('error');

        $this->assertEmpty($videoView->title);
        $this->assertEmpty($videoView->htmlIframe);
    }

    public function testWrongVideoMustNotHaveHtmlIframe(): void
    {
        $videoView = $this->videoUrlViewFactory->create('wrong video url');

        $this->assertEmpty($videoView->htmlIframe);
    }
}
