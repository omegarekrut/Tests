<?php

namespace Tests\Controller\Admin\WaterLevel;

use App\Domain\User\Entity\User;
use App\Domain\WaterLevel\Entity\GaugingStation;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Symfony\Component\HttpFoundation\Response;
use Tests\DataFixtures\ORM\WaterLevel\LoadNovosibirskGaugingStation;

class GaugingStationControllerTest extends TestCase
{
    public function testIndex(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($user instanceof User);

        $client = $this->getBrowser()->loginUser($user);

        $client->request('GET', '/admin/gauging-station/');

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testCreate(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($user instanceof User);

        $client = $this->getBrowser()->loginUser($user);

        $client->request('GET', '/admin/gauging-station/create');

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testEdit(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadNovosibirskGaugingStation::class,
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($user instanceof User);

        $gaugingStation = $referenceRepository->getReference(LoadNovosibirskGaugingStation::REFERENCE_NAME);
        assert($gaugingStation instanceof GaugingStation);

        $gaugingStationId = $gaugingStation->getId();
        $gaugingStationName = $gaugingStation->getName();
        $gaugingStationDistanceFromSource = $gaugingStation->getDistanceFromSource();
        $gaugingStationDistanceToEstuary = $gaugingStation->getDistanceToEstuary();
        $gaugingStationSeaLevelHeight = $gaugingStation->getSeaLevelHeight();
        $gaugingStationWater = $gaugingStation->getWater();
        $gaugingStationCoordinates = $gaugingStation->getGeographicalPosition()->getCoordinates();

        $client = $this->getBrowser()->loginUser($user);

        $crawler = $client->request('GET', sprintf('/admin/gauging-station/%s/update/', $gaugingStationId));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString($gaugingStationName, $crawler->html());
        $this->assertStringContainsString(strval($gaugingStationDistanceFromSource), $crawler->html());
        $this->assertStringContainsString(strval($gaugingStationDistanceToEstuary), $crawler->html());
        $this->assertStringContainsString(strval($gaugingStationSeaLevelHeight), $crawler->html());
        $this->assertStringContainsString(strval($gaugingStationWater), $crawler->html());
        $this->assertStringContainsString(strval($gaugingStationCoordinates), $crawler->html());
    }
}
