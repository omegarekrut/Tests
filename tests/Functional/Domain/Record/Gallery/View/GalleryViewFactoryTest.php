<?php

namespace Tests\Functional\Domain\Record\Gallery\View;

use App\Domain\Category\Entity\Category;
use App\Domain\Comment\Collection\CommentCollection;
use App\Domain\Hashtag\Collection\HashtagCollection;
use App\Domain\Hashtag\Entity\Hashtag;
use App\Domain\Hashtag\Parser\HashtagParser;
use App\Domain\Record\Common\View\RecordViewCommonInformationFiller;
use App\Domain\Record\Gallery\Entity\Gallery;
use App\Domain\Record\Gallery\View\GalleryViewFactory;
use App\Module\Author\AnonymousAuthor;
use App\Module\Voting\Entity\VotableIdentifier;
use App\Twig\DateTime\LocalizedDateTimeFormatter;
use App\Twig\Hashtag\HashtagLinkerFilter;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ImageTransformerFactory;
use App\Util\StringFilter\CleanedTextLineFilter;
use Carbon\Carbon;
use DateTime;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\DataFixtures\ORM\LoadHashtags;
use Tests\DataFixtures\ORM\Record\LoadGallery;
use Tests\Functional\TestCase;
use Laminas\Diactoros\Uri;

/**
 * @group record-view
 */
class GalleryViewFactoryTest extends TestCase
{
    private const GALLERY_URL = 'some/url';

    private Gallery $gallery;
    private GalleryViewFactory $galleryViewFactory;
    private CleanedTextLineFilter $cleanedTextLineFilter;
    private ImageTransformerFactory $imageTransformerFactory;
    private Hashtag $hashtag;
    private HashtagParser $hashtagParser;
    private LocalizedDateTimeFormatter $localizedDateTimeFormatter;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadGallery::class,
            LoadHashtags::class,
        ])->getReferenceRepository();

        $this->gallery = $referenceRepository->getReference(LoadGallery::getRandReferenceName());
        $this->hashtag = $referenceRepository->getReference(LoadHashtags::getReferenceNameBySlug(LoadHashtags::HASHTAG_SLUG_WINTER_FISHING));
        $this->galleryViewFactory = $this->createGalleryViewFactory();
        $this->cleanedTextLineFilter = $this->getContainer()->get(CleanedTextLineFilter::class);
        $this->imageTransformerFactory = $this->getContainer()->get(ImageTransformerFactory::class);
        $this->hashtagParser = $this->getContainer()->get(HashtagParser::class);
        $this->localizedDateTimeFormatter = $this->getContainer()->get(LocalizedDateTimeFormatter::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->gallery,
            $this->hashtag,
            $this->galleryViewFactory,
            $this->recordViewUrlGenerator,
            $this->cleanedTextLineFilter,
            $this->imageTransformerFactory,
            $this->hashtagParser,
            $this->localizedDateTimeFormatter
        );

        parent::tearDown();
    }

    public function testCategoryhouldBeEqualsGalleryCategory(): void
    {
        $galleryView = $this->galleryViewFactory->create($this->gallery);

        $this->assertEquals($this->gallery->getCategory(), $galleryView->category);
    }

    public function testMetadataTitleShouldBeEqualsGalleryTitleWithPhotoSuffix(): void
    {
        $galleryView = $this->galleryViewFactory->create($this->gallery);

        $this->assertEquals($this->gallery->getTitle().' - фото.', $galleryView->metadata->title);
    }

    public function testHeadingShouldBeEqualsGalleryTitle(): void
    {
        $galleryView = $this->galleryViewFactory->create($this->gallery);

        $this->assertEquals($this->gallery->getTitle(), $galleryView->heading);
    }

    public function testMetadataViewUrlShouldLeadToPageForViewingGallery(): void
    {
        $galleryView = $this->galleryViewFactory->create($this->gallery);

        $this->assertEquals(new Uri(self::GALLERY_URL), (string) $galleryView->metadata->viewUrl);
    }

    public function testMetadataDescriptionShouldBeCreatedFromGalleryDescriptionAndTitleAndFishingSuffix(): void
    {
        $gallery = $this->createGalleryWithDescription('simple text');
        $galleryView = $this->galleryViewFactory->create($gallery);

        $this->assertStringContainsString('Фотографии рыбаков', $galleryView->metadata->description);
        $this->assertStringContainsString($gallery->getTitle(), $galleryView->metadata->description);
        $this->assertStringContainsString($gallery->getDescription(), $galleryView->metadata->description);
    }

    public function testPreviewShouldBeCreatedFromGalleryDescription(): void
    {
        $gallery = $this->createGalleryWithDescription('simple text');
        $galleryView = $this->galleryViewFactory->create($gallery);

        $this->assertEquals($gallery->getDescription(), $galleryView->previewText);
    }

    public function testDescriptionForGalleryWithoutDescriptionShouldBeCreatedBySecondaryInformation(): void
    {
        $gallery = $this->createGalleryWithSecondaryInformation('title', 'category', 'author name', Carbon::now());
        $galleryView = $this->galleryViewFactory->create($gallery);

        $this->assertStringContainsString($gallery->getTitle(), $galleryView->metadata->description);
        $this->assertStringContainsString('Фотографии рыбаков', $galleryView->metadata->description);
        $this->assertStringContainsString('Рубрика: '.$gallery->getCategory()->getRoot()->getTitle(), $galleryView->metadata->description);

        $localizedDateTimeFormatter = $this->localizedDateTimeFormatter;
        $expectedPublishDate = $localizedDateTimeFormatter($gallery->getCreatedAt(), 'd MMMM yyyy');

        $this->assertStringContainsString("Размещено $expectedPublishDate года", $galleryView->metadata->description);
        $this->assertStringContainsString('пользователем: '.$gallery->getAuthor()->getUsername(), $galleryView->metadata->description);
    }

    public function testPreviewAndDescriptionShouldClearedByCleanedTextLineFilter(): void
    {
        $gallery = $this->createGalleryWithDescription("not prepared \n\n<b>preview</b> text");
        $galleryView = $this->galleryViewFactory->create($gallery);

        $cleanedTextLineFilter = $this->cleanedTextLineFilter;

        $expectedText = $cleanedTextLineFilter($gallery->getDescription());

        $this->assertStringContainsString($expectedText, $galleryView->metadata->description);
        $this->assertStringContainsString($expectedText, $galleryView->previewText);
    }

    public function testHtmlTextShouldBeCreatedFromGallery(): void
    {
        $gallery = $this->createGalleryWithDescription('simple text');
        $galleryView = $this->galleryViewFactory->create($gallery);

        $this->assertStringContainsString($gallery->getDescription(), $galleryView->htmlText);
    }

    public function testGalleryHtmlTextShouldNotContainsHtmlSpecialCharsFromSource(): void
    {
        $gallery = $this->createGalleryWithDescription('<p>content</p>');
        $galleryView = $this->galleryViewFactory->create($gallery);

        $expectedText = htmlspecialchars($gallery->getDescription());

        $this->assertStringContainsString($expectedText, $galleryView->htmlText);
    }

    public function testLineBreaksInGalleryHtmlShouldBePreparedLikeParagraphTags(): void
    {
        $gallery = $this->createGalleryWithDescription("First line\nSecond line");
        $galleryView = $this->galleryViewFactory->create($gallery);

        $expectedHtmlText = '<p>First line</p><p>Second line</p>';

        $this->assertStringContainsString($expectedHtmlText, $galleryView->htmlText);
    }

    public function testUrlsMustBeLinksInGalleryHtml(): void
    {
        $gallery = $this->createGalleryWithDescription('http://foo.bar');
        $galleryView = $this->galleryViewFactory->create($gallery);

        $expectedHtmlText = '<a href="http://foo.bar" target="_blank" rel="nofollow">http://foo.bar</a>';

        $this->assertStringContainsString($expectedHtmlText, $galleryView->htmlText);
    }

    public function testTagsInHtmlTextShouldBeMarketAndAddedListTagList(): void
    {
        $gallery = $this->createGalleryWithDescription('#'.$this->hashtag->getName());
        $galleryView = $this->galleryViewFactory->create($gallery);

        $expectedHtmlText = $this->hashtagParser->addLinksToHashtags($gallery->getDescription(), new HashtagCollection([$this->hashtag]));

        $this->assertStringContainsString($expectedHtmlText, $galleryView->htmlText);
    }

    public function testViewImageMustBeEqualsGalleryImageTransformer(): void
    {
        $galleryView = $this->galleryViewFactory->create($this->gallery);
        $expectedImage = $this->imageTransformerFactory->create($this->gallery->getImage());

        $this->assertEquals($expectedImage, $galleryView->image);
    }

    private function createGalleryWithDescription(string $description): Gallery
    {
        $stub = $this->createMock(Gallery::class);
        $stub
            ->method('getTitle')
            ->willReturn('some title');
        $stub
            ->method('getDescription')
            ->willReturn($description);
        $stub
            ->method('getImage')
            ->willReturn(new Image('some-image.jpg'));
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

    private function createGalleryWithSecondaryInformation(string $title, string $categoryTitle, string $authorName, DateTime $createdAt): Gallery
    {
        $category = $this->createMock(Category::class);
        $category
            ->method('getTitle')
            ->willReturn($categoryTitle);
        $category
            ->method('getRoot')
            ->willReturn($category);

        $gallery = $this->createMock(Gallery::class);
        $gallery
            ->method('getTitle')
            ->willReturn($title);
        $gallery
            ->method('getDescription')
            ->willReturn('');
        $gallery
            ->method('getCategory')
            ->willReturn($category);
        $gallery
            ->method('getAuthor')
            ->willReturn(new AnonymousAuthor($authorName));
        $gallery
            ->method('getCreatedAt')
            ->willReturn($createdAt);
        $gallery
            ->method('getImage')
            ->willReturn(new Image('some-image.jpg'));
        $gallery
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier('1', 'type'));
        $gallery
            ->method('getComments')
            ->willReturn(new CommentCollection());
        $gallery
            ->method('getCommentsWithAnswers')
            ->willReturn(new CommentCollection());

        return $gallery;
    }

    private function createGalleryViewFactory(): GalleryViewFactory
    {
        $urlGeneratorInterface = $this->createMock(UrlGeneratorInterface::class);
        $urlGeneratorInterface->method('generate')->willReturn(self::GALLERY_URL);

        return new GalleryViewFactory(
            $this->getContainer()->get(CleanedTextLineFilter::class),
            $this->getContainer()->get(ImageTransformerFactory::class),
            $urlGeneratorInterface,
            $this->getContainer()->get(HashtagLinkerFilter::class),
            $this->getContainer()->get(LocalizedDateTimeFormatter::class),
            $this->createMock(RecordViewCommonInformationFiller::class)
        );
    }
}
