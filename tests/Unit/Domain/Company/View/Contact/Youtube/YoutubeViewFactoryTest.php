<?php

namespace Tests\Unit\Domain\Company\View\Contact\Youtube;

use App\Domain\Company\Entity\Contact;
use App\Domain\Company\View\Contact\Youtube\YoutubeUrlView;
use App\Domain\Company\View\Contact\Youtube\YoutubeViewFactory;
use Symfony\Component\Routing\RouterInterface;
use Tests\Unit\TestCase;

class YoutubeViewFactoryTest extends TestCase
{
    private const STATISTIC_LINK_URL = 'statistic_link_url';

    protected YoutubeViewFactory $youtubeViewFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $router = $this->createMock(RouterInterface::class);
        $router->method('generate')
            ->willReturn(self::STATISTIC_LINK_URL);

        $this->youtubeViewFactory = new YoutubeViewFactory($router);
    }

    protected function tearDown(): void
    {
        unset(
            $this->youtubeViewFactory,
        );

        parent::tearDown();
    }

    public function testCreate(): void
    {
        $youtubeUrlView = $this->youtubeViewFactory->createFromContact(
            $this->getContactWithYoutubeLink('https://www.youtube.com/c/ChannelName')
        );

        $this->assertInstanceOf(YoutubeUrlView::class, $youtubeUrlView);
        $this->assertEquals(self::STATISTIC_LINK_URL, $youtubeUrlView->statisticLink);
    }

    public function testCreateWithNullTitle(): void
    {
        $youtube = $this->youtubeViewFactory->createFromContact(
            $this->getContactWithYoutubeLink(null)
        );

        $this->assertNull($youtube);
    }

    /**
     * @dataProvider youtubeUrlProvider
     */
    public function testCreateReadabilityTitle(string $youtubeUrl, string $expectedTitle): void
    {
        $this->assertSame($expectedTitle, $this->youtubeViewFactory->createReadabilityTitle($youtubeUrl));
    }

    /**
     * @return string[][]
     */
    public function youtubeUrlProvider(): array
    {
        return [
            'Default channel with name' => ['https://www.youtube.com/c/ChannelName', 'ChannelName'],
            'Default user with name' => ['https://www.youtube.com/user/UserName', 'UserName'],
            'Default channel without name' => ['https://www.youtube.com/channel/UCSntQAq_k4I4oSKWm_D8mZg', 'Youtube канал'],
            'Video' => ['https://www.youtube.com/watch?v=cR48tSrQsfk', 'Youtube канал'],
            'Short link' => ['youtu.be.com/watch?v=cR48tSrQsfk', 'Youtube канал'],
            'Wrong link' => ['ftp://youtube/domain/video', 'Youtube канал'],
        ];
    }

    private function getContactWithYoutubeLink(?string $youtubeLInk): Contact
    {
        $contact = $this->createMock(Contact::class);
        $contact->method('getYoutube')
            ->willReturn($youtubeLInk);

        return $contact;
    }
}
