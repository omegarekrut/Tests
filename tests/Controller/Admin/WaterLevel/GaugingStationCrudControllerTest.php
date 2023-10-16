<?php

namespace Tests\Controller\Admin\WaterLevel;

use App\Domain\WaterLevel\Entity\GaugingStation;
use App\Domain\WaterLevel\Entity\Water;
use App\Util\Coordinates\Coordinates;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\WaterLevel\LoadBerdWater;
use Tests\DataFixtures\ORM\WaterLevel\LoadNovosibirskGaugingStation;
use Tests\Traits\FakerFactoryTrait;

class GaugingStationCrudControllerTest extends TestCase
{
    use FakerFactoryTrait;

    public function testCreateGaugingStation(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
            LoadBerdWater::class,
        ])->getReferenceRepository();

        $admin = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($admin instanceof User);

        $water = $referenceRepository->getReference(LoadBerdWater::REFERENCE_NAME);
        assert($water instanceof Water);

        $name = $this->getFaker()->realText(20);
        $distanceFromSourceInKilometers = 100;
        $distanceToEstuaryInKilometers = 100;
        $seaLevelHeight = 100;
        $coordinates = new Coordinates(89,179);

        $browser = $this->getBrowser()->loginUser($admin);

        $browser->request('GET', '/admin/gauging-station/create');

        $browser->submitForm('Сохранить', [
            'gauging_station_create[name]' => $name,
            'gauging_station_create[distanceFromSourceInKilometers]' => $distanceFromSourceInKilometers,
            'gauging_station_create[distanceToEstuaryInKilometers]' => $distanceToEstuaryInKilometers,
            'gauging_station_create[seaLevelHeight]' => $seaLevelHeight,
            'gauging_station_create[water]' => $water->getId(),
            'gauging_station_create[coordinates]' => $coordinates,
        ]);

        $this->assertTrue($browser->getResponse()->isRedirect('/admin/gauging-station/'));

        $viewPage = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', 'Гидропост успешно создан.', $viewPage->html());

        $this->assertStringContainsString($name, $viewPage->html());
    }

    public function testEditGaugingStation(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
            LoadNovosibirskGaugingStation::class,
            LoadBerdWater::class,
        ])->getReferenceRepository();

        $admin = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($admin instanceof User);

        $water = $referenceRepository->getReference(LoadBerdWater::REFERENCE_NAME);
        assert($water instanceof Water);

        $gaugingStation = $referenceRepository->getReference(LoadNovosibirskGaugingStation::REFERENCE_NAME);
        assert($gaugingStation instanceof GaugingStation);

        $name = $this->getFaker()->realText(20);
        $distanceFromSourceInKilometers = 100;
        $distanceToEstuaryInKilometers = 100;
        $seaLevelHeight = 100;
        $coordinates = new Coordinates(89,179);

        $browser = $this->getBrowser()->loginUser($admin);

        $browser->request('GET', sprintf('/admin/gauging-station/%s/update/', $gaugingStation->getId()));

        $browser->submitForm('Сохранить', [
            'gauging_station_update[name]' => $name,
            'gauging_station_update[distanceFromSourceInKilometers]' => $distanceFromSourceInKilometers,
            'gauging_station_update[distanceToEstuaryInKilometers]' => $distanceToEstuaryInKilometers,
            'gauging_station_update[seaLevelHeight]' => $seaLevelHeight,
            'gauging_station_update[water]' => $water->getId(),
            'gauging_station_update[coordinates]' => $coordinates,
        ]);

        $this->assertTrue($browser->getResponse()->isRedirect('/admin/gauging-station/'));

        $viewPage = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', 'Гидропост успешно обновлен.', $viewPage->html());

        $this->assertStringContainsString($name, $viewPage->html());
    }
}
