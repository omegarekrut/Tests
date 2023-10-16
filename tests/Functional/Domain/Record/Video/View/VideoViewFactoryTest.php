<?php

namespace Tests\Functional\Domain\Record\Video\View;

use App\Domain\Author\View\AuthorView;
use App\Domain\Category\Entity\Category;
use App\Domain\Comment\Collection\CommentCollection;
use App\Domain\Hashtag\Collection\HashtagCollection;
use App\Domain\Hashtag\Entity\Hashtag;
use App\Domain\Hashtag\Parser\HashtagParser;
use App\Domain\Record\Common\View\RecordViewCommonInformationFiller;
use App\Domain\Record\Common\View\RecordViewUrlGenerator;
use App\Domain\Record\Video\Entity\Video;
use App\Domain\Record\Video\View\VideoViewFactory;
use App\Module\Author\AnonymousAuthor;
use App\Module\Author\AuthorInterface;
use App\Module\Voting\Entity\VotableIdentifier;
use App\Twig\Hashtag\HashtagLinkerFilter;
use App\Util\ImageStorage\ImageTransformerFactory;
use App\Util\StringFilter\CleanedTextLineFilter;
use Carbon\Carbon;
use DateTime;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\DataFixtures\ORM\LoadHashtags;
use Tests\DataFixtures\ORM\Record\LoadVideos;
use Tests\Functional\TestCase;

/**
 * @group record-view
 */
class VideoViewFactoryTest extends TestCase
{
    private const VIDEO_URL = 'some/url';

    private Video $video;
    private VideoViewFactory $videoViewFactory;
    private RecordViewUrlGenerator $recordViewUrlGenerator;
    private CleanedTextLineFilter $cleanedTextLineFilter;
    private ImageTransformerFactory $imageTransformerFactory;
    private Hashtag $hashtag;
    private HashtagParser $hashtagParser;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadVideos::class,
            LoadHashtags::class,
        ])->getReferenceRepository();

        $this->video = $referenceRepository->getReference(LoadVideos::getRandReferenceName());
        $this->hashtag = $referenceRepository->getReference(LoadHashtags::getReferenceNameBySlug(LoadHashtags::HASHTAG_SLUG_WINTER_FISHING));
        $this->videoViewFactory = $this->getContainer()->get(VideoViewFactory::class);
        $this->recordViewUrlGenerator = $this->getContainer()->get(RecordViewUrlGenerator::class);
        $this->cleanedTextLineFilter = $this->getContainer()->get(CleanedTextLineFilter::class);
        $this->imageTransformerFactory = $this->getContainer()->get(ImageTransformerFactory::class);
        $this->hashtagParser = $this->getContainer()->get(HashtagParser::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->video,
            $this->hashtag,
            $this->videoViewFactory,
            $this->recordViewUrlGenerator,
            $this->cleanedTextLineFilter,
            $this->imageTransformerFactory,
            $this->hashtagParser
        );

        parent::tearDown();
    }

    public function testCategoryShouldBeEqualsVideoCategory(): void
    {
        $videoView = $this->videoViewFactory->create($this->video);

        $this->assertEquals($this->video->getCategory(), $videoView->category);
    }

    public function testMetadataTitleShouldBeEqualsVideoTitleWithVideoOnlineSuffix(): void
    {
        $videoView = $this->videoViewFactory->create($this->video);

        $this->assertEquals($this->video->getTitle().' - видео онлайн', $videoView->metadata->title);
    }

    public function testHeadingShouldBeEqualsVideoTitle(): void
    {
        $videoView = $this->videoViewFactory->create($this->video);

        $this->assertEquals($this->video->getTitle(), $videoView->heading);
    }

    public function testMetadataViewUrlShouldLeadToPageForViewingVideo(): void
    {
        $videoView = $this->videoViewFactory->create($this->video);
        $viewVideoPageUrl = $this->recordViewUrlGenerator->generate($this->video);

        $this->assertEquals($viewVideoPageUrl, (string) $videoView->metadata->viewUrl);
    }

    public function testMetadataDescriptionShouldBeCreatedFromVideoDescriptionWithDetails(): void
    {
        $video = $this->createVideoDetails(
            'title',
            'category',
            'simple description',
            new AnonymousAuthor('author'),
            Carbon::now()
        );
        $videoView = $this->videoViewFactory->create($video);

        $this->assertStringContainsString('Видео: '.$video->getTitle(), $videoView->metadata->description);
        $this->assertStringContainsString('смотреть онлайн бесплатно', $videoView->metadata->description);
        $this->assertStringContainsString('Рубрика: '.$video->getCategory()->getRoot()->getTitle(), $videoView->metadata->description);
        $this->assertStringContainsString('Добавлено пользователем '.$video->getAuthor()->getUsername(), $videoView->metadata->description);
        $this->assertStringContainsString('в '.$video->getCreatedAt()->format('Y').' году', $videoView->metadata->description);
        $this->assertStringContainsString($video->getDescription(), $videoView->metadata->description);
    }

    public function testPreviewShouldBeCreatedFromVideoDescription(): void
    {
        $video = $this->createVideoWithDescription('description');
        $videoView = $this->videoViewFactory->create($video);

        $this->assertEquals($video->getDescription(), $videoView->previewText);
    }

    public function testDescriptionAndPreviewShouldClearedByCleanedTextLineFilter(): void
    {
        $video = $this->createVideoWithDescription("not prepared \n\n<b>preview</b> text");
        $videoView = $this->videoViewFactory->create($video);

        $cleanedTextLineFilter = $this->cleanedTextLineFilter;

        $this->assertStringContainsString($cleanedTextLineFilter($video->getDescription()), $videoView->metadata->description);
        $this->assertStringContainsString($cleanedTextLineFilter($video->getDescription()), $videoView->previewText);
    }

    public function testHtmlTextShouldBeCreatedFromVideo(): void
    {
        $video = $this->createVideoWithDescription('simple text');
        $videoView = $this->videoViewFactory->create($video);

        $this->assertStringContainsString($video->getDescription(), $videoView->htmlText);
    }

    public function testVideoHtmlTextShouldNotContainsHtmlSpecialCharsFromSource(): void
    {
        $video = $this->createVideoWithDescription('<p>content</p>');
        $videoView = $this->videoViewFactory->create($video);

        $expectedText = htmlspecialchars($video->getDescription());

        $this->assertStringContainsString($expectedText, $videoView->htmlText);
    }

    public function testLineBreaksInVideoHtmlShouldBePreparedLikeBrTags(): void
    {
        $video = $this->createVideoWithDescription("First line\nSecond line");
        $videoView = $this->videoViewFactory->create($video);

        $expectedHtmlText = "First line<br />\nSecond line";

        $this->assertStringContainsString($expectedHtmlText, $videoView->htmlText);
    }

    public function testUrlsMustBeLinksInVideoHtml(): void
    {
        $video = $this->createVideoWithDescription('http://foo.bar');
        $videoView = $this->videoViewFactory->create($video);

        $expectedHtmlText = '<a href="http://foo.bar" target="_blank" rel="nofollow">http://foo.bar</a>';

        $this->assertStringContainsString($expectedHtmlText, $videoView->htmlText);
    }

    public function testTagsInHtmlTextShouldBeMarketAndAddedListTagList(): void
    {
        $video = $this->createVideoWithDescription('#'.$this->hashtag->getName());
        $videoView = $this->videoViewFactory->create($video);

        $expectedHtmlText = $this->hashtagParser->addLinksToHashtags($video->getDescription(), new HashtagCollection([$this->hashtag]));

        $this->assertStringContainsString($expectedHtmlText, $videoView->htmlText);
    }

    public function testViewImageMustBeEqualsVideoImageTransformer(): void
    {
        $videoView = $this->videoViewFactory->create($this->video);
        $expectedImage = $this->imageTransformerFactory->create($this->video->getImage());

        $this->assertStringContainsString($expectedImage, $videoView->image);
    }

    public function testViewHtmlIframeMustBeEqualsVideoCode(): void
    {
        $videoView = $this->videoViewFactory->create($this->video);

        $this->assertEquals($this->video->getIframe(), $videoView->htmlIframe);
    }

    private function createVideoWithDescription(string $description): Video
    {
        $stub = $this->createMock(Video::class);
        $stub
            ->method('getDescription')
            ->willReturn($description);
        $stub
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier('1', 'type'));
        $stub
            ->method('getComments')
            ->willReturn(new CommentCollection());
        $stub
            ->method('getCommentsWithAnswers')
            ->willReturn(new CommentCollection());

        return $stub;
    }

    private function createVideoDetails(string $title, string $categoryTitle, string $description, AuthorInterface $author, DateTime $createdAt): Video
    {
        $category = $this->createMock(Category::class);
        $category
            ->method('getTitle')
            ->willReturn($categoryTitle);
        $category
            ->method('getRoot')
            ->willReturn($category);

        $video =  $this->createMock(Video::class);
        $video
            ->method('getTitle')
            ->willReturn($title);
        $video
            ->method('getCategory')
            ->willReturn($category);
        $video
            ->method('getDescription')
            ->willReturn($description);
        $video
            ->method('getAuthor')
            ->willReturn($author);
        $video
            ->method('getCreatedAt')
            ->willReturn($createdAt);
        $video
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier('1', 'type'));
        $video
            ->method('getComments')
            ->willReturn(new CommentCollection());
        $video
            ->method('getCommentsWithAnswers')
            ->willReturn(new CommentCollection());

        return $video;
    }

    private function createVideoViewFactory(): VideoViewFactory
    {
        $urlGeneratorInterface = $this->createMock(UrlGeneratorInterface::class);
        $urlGeneratorInterface->method('generate')->willReturn(self::VIDEO_URL);

        return new VideoViewFactory(
            $this->getContainer()->get(CleanedTextLineFilter::class),
            $this->getContainer()->get(ImageTransformerFactory::class),
            $urlGeneratorInterface,
            $this->getContainer()->get(HashtagLinkerFilter::class),
            $this->createMock(RecordViewCommonInformationFiller::class)
        );
    }
}
