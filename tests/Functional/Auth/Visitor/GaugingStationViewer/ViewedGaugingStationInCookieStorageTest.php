<?php

namespace Tests\Functional\Auth\Visitor\GaugingStationViewer;

use App\Auth\Visitor\GaugingStationViewer\ViewedGaugingStationInCookieStorage;
use App\Domain\WaterLevel\Entity\GaugingStation;
use App\Util\Cookie\CookieCollection;
use App\Util\Coordinates\Coordinates;
use Tests\DataFixtures\ORM\WaterLevel\LoadBerdskGaugingStation;
use Tests\DataFixtures\ORM\WaterLevel\LoadNovosibirskGaugingStation;
use Tests\Functional\TestCase;

class ViewedGaugingStationInCookieStorageTest extends TestCase
{
    /** @var GaugingStation */
    private $berdskGaugingStation;
    /** @var GaugingStation */
    private $novosibirskGaugingStation;
    private $cookieCollection;
    private $viewedGaugingStationInCookieStorage;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadBerdskGaugingStation::class,
            LoadNovosibirskGaugingStation::class,
        ])->getReferenceRepository();

        /** @var GaugingStation $berdskGaugingStation */
        $this->berdskGaugingStation = $referenceRepository->getReference(LoadBerdskGaugingStation::REFERENCE_NAME);
        /** @var GaugingStation $nskGaugingStation */
        $this->novosibirskGaugingStation = $referenceRepository->getReference(LoadNovosibirskGaugingStation::REFERENCE_NAME);

        $this->viewedGaugingStationInCookieStorage = $this->getContainer()->get(ViewedGaugingStationInCookieStorage::class);
        $this->cookieCollection = $this->getContainer()->get(CookieCollection::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->cookieCollection,
            $this->viewedGaugingStationInCookieStorage,
            $this->novosibirskGaugingStation,
            $this->berdskGaugingStation
        );

        parent::tearDown();
    }

    public function testStationCanBeAddedToStorageAndSaveInCookie(): void
    {
        $this->viewedGaugingStationInCookieStorage->addStation($this->berdskGaugingStation);

        $viewedStationIds = $this->getViewedGaugingStationsFromCookie();

        $this->assertCount(1, $viewedStationIds);
        $this->assertEquals((string) $this->berdskGaugingStation->getId(), $viewedStationIds[0]);
    }

    public function testStationCanBeRemoveFromStorageAndFromCookie(): void
    {
        $this->viewedGaugingStationInCookieStorage->addStation($this->berdskGaugingStation);

        $this->viewedGaugingStationInCookieStorage->removeStation($this->berdskGaugingStation);

        $viewedStationIds = $this->getViewedGaugingStationsFromCookie();

        $this->assertCount(0, $viewedStationIds);
    }

    public function testEarlierAddedStationPlacedInTheBeginningOfStorageOnNewAdd(): void
    {
        $this->viewedGaugingStationInCookieStorage->addStation($this->berdskGaugingStation);
        $this->viewedGaugingStationInCookieStorage->addStation($this->novosibirskGaugingStation);
        $viewedStationIds = $this->getViewedGaugingStationsFromCookie();

        $this->assertCount(2, $viewedStationIds);
        $this->assertEquals((string) $this->berdskGaugingStation->getId(), $viewedStationIds[0]);
        $this->assertEquals((string) $this->novosibirskGaugingStation->getId(), $viewedStationIds[1]);

        $this->viewedGaugingStationInCookieStorage->addStation($this->berdskGaugingStation);
        $viewedStationIds = $this->getViewedGaugingStationsFromCookie();

        $this->assertCount(2, $viewedStationIds);
        $this->assertEquals((string) $this->novosibirskGaugingStation->getId(), $viewedStationIds[0]);
        $this->assertEquals((string) $this->berdskGaugingStation->getId(), $viewedStationIds[1]);
    }

    public function testGetViewedOrClosestToCoordinateReturnsViewed(): void
    {
        $this->viewedGaugingStationInCookieStorage->addStation($this->novosibirskGaugingStation);
        $this->viewedGaugingStationInCookieStorage->addStation($this->berdskGaugingStation);

        $viewedStationIds = $this->getViewedGaugingStationsFromCookie();

        $this->assertCount(2, $viewedStationIds);
        $this->assertEquals((string) $this->novosibirskGaugingStation->getId(), $viewedStationIds[0]);
        $this->assertEquals((string) $this->berdskGaugingStation->getId(), $viewedStationIds[1]);
    }

    public function testGetViewedOrClosestToCoordinateReturnsClosestToCoordinate(): void
    {
        $this->assertEmpty($this->getViewedGaugingStationsFromCookie());

        $visitorCoordinates = new Coordinates(55.027434, 82.918087);

        $viewedStations = $this->viewedGaugingStationInCookieStorage
            ->getViewedOrClosestToCoordinate(3, $visitorCoordinates);

        $this->assertCount(2, $viewedStations);
        $this->assertEquals((string) $this->novosibirskGaugingStation->getId(), $viewedStations->first()->getId());
        $this->assertEquals((string) $this->berdskGaugingStation->getId(), $viewedStations->next()->getId());

        $viewedStationIds = $this->getViewedGaugingStationsFromCookie();

        $this->assertCount(2, $viewedStationIds);
        $this->assertEquals((string) $this->berdskGaugingStation->getId(), $viewedStationIds[0]);
        $this->assertEquals((string) $this->novosibirskGaugingStation->getId(), $viewedStationIds[1]);
    }

    /**
     * @return string[]
     */
    private function getViewedGaugingStationsFromCookie(): array
    {
        $cookie = $this->cookieCollection->get(ViewedGaugingStationInCookieStorage::VIEWED_GAUGING_STATIONS_COOKIE_KEY);

        if (!$cookie || $cookie->isDelete()) {
            return [];
        }

        $stationIds = explode(ViewedGaugingStationInCookieStorage::VIEWED_GAUGING_STATION_IDS_DELIMITER, $cookie->getValue());

        return array_filter($stationIds);
    }
}
