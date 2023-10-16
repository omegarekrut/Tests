<?php

namespace Tests\Functional\Domain\Record\Common\View\Collection;

use App\Domain\Record\Common\View\Collection\VideoUrlViewCollection;
use App\Domain\Record\Common\View\VideoUrlView;
use Tests\Unit\TestCase;

class VideoUrlViewCollectionTest extends TestCase
{
    private const USER_VIDEO_HTML_IFRAME = '<iframe width="200" height="113" src="https://www.youtube.com/embed/dQw4w9WgXcQ?feature=oembed" 
        frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
    private const UNUSED_VIDEO_HTML_IFRAME = '<iframe width="200" height="113" src="https://www.youtube.com/embed/edtkzFSa4gI?feature=oembed" 
        frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';

    public function testFindAllUnusedInText(): void
    {
        $videoUrlViews = $this->getVideoUrlViewCollection();

        $unusedInTextVideos = $videoUrlViews->findAllUnusedInText($this->getArticleText());

        $this->assertEquals(2, $unusedInTextVideos->count());
        $this->assertNull($unusedInTextVideos[0]->htmlIframe);
        $this->assertEquals(self::UNUSED_VIDEO_HTML_IFRAME, $unusedInTextVideos[1]->htmlIframe);
    }

    private function getVideoUrlViewCollection(): VideoUrlViewCollection
    {
        $videoCollection = new VideoUrlViewCollection();

        $videoCollection->add($this->getVideoView(null));
        $videoCollection->add($this->getVideoView(self::USER_VIDEO_HTML_IFRAME));
        $videoCollection->add($this->getVideoView(self::UNUSED_VIDEO_HTML_IFRAME));

        return $videoCollection;
    }

    private function getVideoView(?string $htmlIframe): VideoUrlView
    {
        $mock = $this->createMock(VideoUrlView::class);
        $mock->htmlIframe = $htmlIframe;

        return $mock;
    }

    private function getArticleText(): string
    {
        return '
        Обь встала и я решил уехать подальше, взял с собой на обучение трёх "новичков"<br />
        
        <div class="mb15">
            <div class="js-has-video">
                <iframe width="640" height="360" src="//www.youtube.com/embed/dQw4w9WgXcQ?wmode=opaque" data-youtube-id="dQw4w9WgXcQ" frameborder="0" allowfullscreen></iframe>
            </div>
        </div>
        <br />
        К обеду натыкаемся на отличный пятак просто конской рыбы...
        ';
    }
}
