<?php

namespace Tests\Functional\Domain\Record\Map\View;

use App\Domain\Comment\Collection\CommentCollection;
use App\Domain\Record\Common\View\RecordViewCommonInformationFiller;
use App\Domain\Record\Common\View\RecordViewUrlGenerator;
use App\Domain\Record\Map\Entity\Map;
use App\Domain\Record\Map\View\MapViewFactory;
use App\Module\Voting\Entity\VotableIdentifier;
use App\Util\StringFilter\CleanedTextLineFilter;
use Laminas\Diactoros\Uri;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\DataFixtures\ORM\Record\LoadMaps;
use Tests\Functional\TestCase;

/**
 * @group record-view
 */
class MapViewFactoryTest extends TestCase
{
    private const MAP_URL = 'some/url';

    private Map $map;
    private MapViewFactory $mapViewFactory;
    private RecordViewUrlGenerator $recordViewUrlGenerator;
    private CleanedTextLineFilter $cleanedTextLineFilter;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadMaps::class,
        ])->getReferenceRepository();

        $this->map = $referenceRepository->getReference(LoadMaps::getRandReferenceName());
        $this->mapViewFactory = $this->createMapViewFactory();
        $this->recordViewUrlGenerator = $this->getContainer()->get(RecordViewUrlGenerator::class);
        $this->cleanedTextLineFilter = $this->getContainer()->get(CleanedTextLineFilter::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->map,
            $this->mapViewFactory,
            $this->recordViewUrlGenerator,
            $this->cleanedTextLineFilter
        );

        parent::tearDown();
    }

    public function testMetadataTitleShouldBeEqualsMapTitle(): void
    {
        $mapView = $this->mapViewFactory->create($this->map);

        $this->assertEquals($this->map->getTitle(), $mapView->metadata->title);
    }

    public function testHeadingShouldBeEqualsMapTitle(): void
    {
        $mapView = $this->mapViewFactory->create($this->map);

        $this->assertEquals($this->map->getTitle(), $mapView->heading);
    }

    public function testMetadataViewUrlShouldLeadToPageForViewingMap(): void
    {
        $mapView = $this->mapViewFactory->create($this->map);

        $this->assertEquals(new Uri(self::MAP_URL), (string) $mapView->metadata->viewUrl);
    }

    public function testPreviewAndDescriptionAndHtmlTextShouldBeCreatedFromMapDescription(): void
    {
        $map = $this->createMapWithDescription('simple text');
        $mapView = $this->mapViewFactory->create($map);

        $this->assertEquals($map->getDescription(), $mapView->metadata->description);
        $this->assertEquals($map->getDescription(), $mapView->htmlText);
        $this->assertEquals($map->getDescription(), $mapView->previewText);
    }

    public function testMetadataDescriptionAndPreviewShouldClearedByCleanedTextLineFilter(): void
    {
        $map = $this->createMapWithDescription("not prepared \n\n<b>preview</b> text");
        $mapView = $this->mapViewFactory->create($map);

        $cleanedTextLineFilter = $this->cleanedTextLineFilter;

        $this->assertEquals($cleanedTextLineFilter($map->getDescription()), $mapView->metadata->description);
        $this->assertEquals($cleanedTextLineFilter($map->getDescription()), $mapView->previewText);
    }

    public function testMapHtmlTextShouldNotContainsHtmlSpecialCharsFromSource(): void
    {
        $map = $this->createMapWithDescription('<p>content</p>');
        $mapView = $this->mapViewFactory->create($map);

        $expectedText = htmlspecialchars($map->getDescription());

        $this->assertStringContainsString($expectedText, $mapView->htmlText);
    }

    public function testViewCoordinatesMustBeEqualsMapCoordinates(): void
    {
        $mapView = $this->mapViewFactory->create($this->map);

        $this->assertEquals($this->map->getCoordinates(), $mapView->coordinates);
    }

    private function createMapWithDescription(string $description): Map
    {
        $stub = $this->createMock(Map::class);
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

    private function createMapViewFactory(): MapViewFactory
    {
        $urlGeneratorInterface = $this->createMock(UrlGeneratorInterface::class);
        $urlGeneratorInterface->method('generate')->willReturn(self::MAP_URL);

        return new MapViewFactory(
            $this->getContainer()->get(CleanedTextLineFilter::class),
            $urlGeneratorInterface,
            $this->createMock(RecordViewCommonInformationFiller::class)
        );
    }
}
