<?php

namespace Tests\Controller\WaterLevel;

use App\Domain\User\Entity\User;
use App\Domain\WaterLevel\Entity\GaugingStation;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\WaterLevel\LoadBerdskGaugingStation;
use Tests\DataFixtures\ORM\WaterLevel\LoadHideGornoAltayskGaugingStation;
use Tests\DataFixtures\ORM\WaterLevel\LoadInactiveBarnaulGaugingStation;
use Tests\DataFixtures\ORM\WaterLevel\LoadNovosibirskGaugingStation;
use Symfony\Component\HttpFoundation\Response;
use Tests\DataFixtures\ORM\WaterLevel\LoadNovosibirskHydroelectricPowerStationGaugingStation;

class GaugingStationAccessControllerTest extends TestCase
{
    public function testAllowGaugingStationPage(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadNovosibirskHydroelectricPowerStationGaugingStation::class,
            LoadNovosibirskGaugingStation::class,
        ])->getReferenceRepository();

        $gaugingStation = $referenceRepository->getReference(LoadNovosibirskGaugingStation::REFERENCE_NAME);
        assert($gaugingStation instanceof GaugingStation);

        $browser = $this->getBrowser();

        $browser->request('GET', sprintf('/waterinfo/gauging-station/%s/%s/', $gaugingStation->getSlug(), $gaugingStation->getShortUuid()));

        $this->assertEquals(Response::HTTP_OK, $browser->getResponse()->getStatusCode());
        $this->assertStringContainsString('По данным ', $browser->getResponse()->getContent());
        $this->assertStringContainsString('График уровня воды', $browser->getResponse()->getContent());
        $this->assertStringContainsString('Уровень воды по данным за', $browser->getResponse()->getContent());
        $this->assertStringContainsString('Гидропост', $browser->getResponse()->getContent());
        $this->assertStringContainsString('Уровень воды на других гидропостах', $browser->getResponse()->getContent());
    }

    public function testOnlyGaugingStationPage(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadBerdskGaugingStation::class,
        ])->getReferenceRepository();

        $gaugingStation = $referenceRepository->getReference(LoadBerdskGaugingStation::REFERENCE_NAME);
        assert($gaugingStation instanceof GaugingStation);

        $browser = $this->getBrowser();

        $browser->request('GET', sprintf('/waterinfo/gauging-station/%s/%s/', $gaugingStation->getSlug(), $gaugingStation->getShortUuid()));

        $this->assertEquals(Response::HTTP_OK, $browser->getResponse()->getStatusCode());
        $this->assertStringNotContainsString('Уровень воды на других гидропостах', $browser->getResponse()->getContent());
    }

    public function testInactivateGaugingStationPage(): void
    {
        $referencerRepository = $this->loadFixtures([
            LoadInactiveBarnaulGaugingStation::class,
            LoadNovosibirskGaugingStation::class,
        ])->getReferenceRepository();

        $gaugingStation = $referencerRepository->getReference(LoadInactiveBarnaulGaugingStation::REFERENCE_NAME);
        assert($gaugingStation instanceof GaugingStation);

        $browser = $this->getBrowser();

        $page = $browser->request('GET', sprintf('/waterinfo/gauging-station/%s/%s/', $gaugingStation->getSlug(), $gaugingStation->getShortUuid()));

        $this->assertEquals(Response::HTTP_OK, $browser->getResponse()->getStatusCode());
        $this->assertStringContainsString('Архивные данные', $page->filter('.contentFS__header--left h1')->text());
        $this->assertStringContainsString('Уровень воды на других гидропостах', $browser->getResponse()->getContent());
        $this->assertStringContainsString('График уровня воды', $browser->getResponse()->getContent());
        $this->assertStringContainsString('Уровень воды по данным за', $browser->getResponse()->getContent());
        $this->assertStringContainsString('Гидропост. ', $browser->getResponse()->getContent());
    }

    public function testGaugingStationWithWrongSlugPage(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadBerdskGaugingStation::class,
        ])->getReferenceRepository();

        $gaugingStation = $referenceRepository->getReference(LoadBerdskGaugingStation::REFERENCE_NAME);
        assert($gaugingStation instanceof GaugingStation);

        $gaugingStationPageCorrectUrl = sprintf('/waterinfo/gauging-station/%s/%s/', $gaugingStation->getSlug(), $gaugingStation->getShortUuid());
        $gaugingStationPageWrongUrl = sprintf('/waterinfo/gauging-station/wrong-slug/%s/', $gaugingStation->getShortUuid());

        $browser = $this->getBrowser();

        $browser->request('GET', $gaugingStationPageWrongUrl);

        $this->assertEquals(Response::HTTP_OK, $browser->getResponse()->getStatusCode());
        $this->assertStringContainsString('<link rel="canonical"', $browser->getResponse()->getContent());
        $this->assertStringContainsString($gaugingStationPageCorrectUrl, $browser->getResponse()->getContent());
    }

    public function testHiddenGaugingStationWithPage(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadHideGornoAltayskGaugingStation::class,
        ])->getReferenceRepository();

        $gaugingStation = $referenceRepository->getReference(LoadHideGornoAltayskGaugingStation::REFERENCE_NAME);
        assert($gaugingStation instanceof GaugingStation);

        $browser = $this->getBrowser();

        $browser->request('GET', sprintf('/waterinfo/gauging-station/%s/%s/', $gaugingStation->getSlug(), $gaugingStation->getShortUuid()));
        $this->assertEquals(Response::HTTP_NOT_FOUND, $browser->getResponse()->getStatusCode());
    }

    public function testFindForAutocomplete(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
            LoadNovosibirskGaugingStation::class,
        ])->getReferenceRepository();

        $admin = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($admin instanceof User);

        $gaugingStation = $referenceRepository->getReference(LoadNovosibirskGaugingStation::REFERENCE_NAME);
        assert($gaugingStation instanceof GaugingStation);

        $client = $this->getBrowser()->loginUser($admin);
        $url = 'admin/gauging-station/autocomplete/?q=овосиб';

        $client->xmlHttpRequest('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString($gaugingStation->getId()->toString(), $this->getBrowser()->getResponse()->getContent());
    }
}
