<?php

namespace Tests\Controller\WaterLevel;

use App\Domain\WaterLevel\Entity\GaugingStation;
use App\Domain\WaterLevel\Entity\Water;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\WaterLevel\LoadBerdskGaugingStation;
use Tests\DataFixtures\ORM\WaterLevel\LoadHideNovosibirskGaugingStation;
use Tests\DataFixtures\ORM\WaterLevel\LoadNovosibirskGaugingStation;
use Tests\DataFixtures\ORM\WaterLevel\LoadObskoeReservoirWater;
use Tests\DataFixtures\ORM\WaterLevel\LoadObWater;

class WaterControllerTest extends TestCase
{
    public function testViewWaterWithGaugingStations(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadBerdskGaugingStation::class,
        ])->getReferenceRepository();

        $gaugingStation = $referenceRepository->getReference(LoadBerdskGaugingStation::REFERENCE_NAME);
        assert($gaugingStation instanceof GaugingStation);

        $browser = $this->getBrowser();

        $page = $browser->request('GET', sprintf('/waterinfo/water/%s/', $gaugingStation->getWater()->getSlug()));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertEquals($gaugingStation->getName(), $page->filter('.gaugingStationTile__city-text__content')->text());
        $this->assertStringContainsString('Уровень воды сегодня, на графике', $page->filter('title')->text());
    }

    public function testViewWaterWithHiddenGaugingStations(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadHideNovosibirskGaugingStation::class,
            LoadNovosibirskGaugingStation::class,
            LoadObWater::class,
        ])->getReferenceRepository();

        $obWater = $referenceRepository->getReference(LoadObWater::REFERENCE_NAME);
        assert($obWater instanceof Water);

        $gaugingStation = $referenceRepository->getReference(LoadNovosibirskGaugingStation::REFERENCE_NAME);
        assert($gaugingStation instanceof GaugingStation);

        $browser = $this->getBrowser();

        $page = $browser->request('GET', sprintf('/waterinfo/water/%s/', $obWater->getSlug()));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertEquals($gaugingStation->getName(), $page->filter('.gaugingStationTile__city-text__content')->text());
        $this->assertStringContainsString('Уровень воды сегодня, на графике', $page->filter('title')->text());
    }

    public function testViewWaterWithOnlyHiddenGaugingStations(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadHideNovosibirskGaugingStation::class,
        ])->getReferenceRepository();

        $hiddenGaugingStation = $referenceRepository->getReference(LoadHideNovosibirskGaugingStation::REFERENCE_NAME);
        assert($hiddenGaugingStation instanceof GaugingStation);

        $browser = $this->getBrowser();

        $browser->request('GET', sprintf('/waterinfo/water/%s/', $hiddenGaugingStation->getWater()->getSlug()));

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testViewWaterWithoutGaugingStations(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadObskoeReservoirWater::class,
        ])->getReferenceRepository();

        $waterWithoutGaugingStation = $referenceRepository->getReference(LoadObskoeReservoirWater::REFERENCE_NAME);
        assert($waterWithoutGaugingStation instanceof Water);

        $browser = $this->getBrowser();

        $browser->request('GET', sprintf('/waterinfo/water/%s/', $waterWithoutGaugingStation->getSlug()));

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testViewGaugingStationForGuest(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadBerdskGaugingStation::class,
        ])->getReferenceRepository();

        $gaugingStation = $referenceRepository->getReference(LoadBerdskGaugingStation::REFERENCE_NAME);
        assert($gaugingStation instanceof GaugingStation);

        $browser = $this->getBrowser();

        $browser->request('GET', sprintf('/waterinfo/gauging-station/%s/%s/', $gaugingStation->getName(), $gaugingStation->getShortUuid()));

        $this->assertEquals(Response::HTTP_OK, $browser->getResponse()->getStatusCode());
        $this->assertStringContainsString($gaugingStation->getName(), $browser->getResponse()->getContent());
    }

    public function testAjaxWaterSearchByQuery(): void
    {
        $this->loadFixtures([
            LoadNovosibirskGaugingStation::class,
        ])->getReferenceRepository();

        $browser = $this->getBrowser();

        $browser->request('GET','/waterinfo/search/?q=Об');

        $this->assertEquals(Response::HTTP_OK, $browser->getResponse()->getStatusCode());
        $this->assertStringContainsString('slug', $browser->getResponse()->getContent());
    }
}
