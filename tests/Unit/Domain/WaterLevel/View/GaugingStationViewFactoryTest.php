<?php

namespace Tests\Unit\Domain\WaterLevel\View;

use App\Domain\WaterLevel\Entity\GaugingStation;
use App\Domain\WaterLevel\Entity\GaugingStationProvider;
use App\Domain\WaterLevel\Entity\GaugingStationProviderRecord;
use App\Domain\WaterLevel\Entity\ValueObject\ExternalIdentifier;
use App\Domain\WaterLevel\Entity\ValueObject\GeographicalPosition;
use App\Domain\WaterLevel\Entity\Water;
use App\Domain\WaterLevel\Repository\GaugingStationRecordRepository;
use App\Domain\WaterLevel\View\GaugingStationViewFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\Unit\TestCase;

class GaugingStationViewFactoryTest extends TestCase
{
    public function testCreateViewObjectFromEntity(): void
    {
        $factory = new GaugingStationViewFactory(
            $this->getUrlGenerator(),
            $this->getGaugingStationRecordRepository(),
        );

        $view = $factory->create($this->createEntity());

        $this->assertNotEmpty($view->id);
        $this->assertEquals('Some name', $view->name);
        $this->assertEquals('/link-to-view', $view->viewPath);
        $this->assertTrue($view->isActive);
        $this->assertNotEmpty($view->geographicalPosition);
        $this->assertEquals(42, $view->distanceFromSourceInKilometers);
        $this->assertNotEmpty($view->distanceToEstuary);
        $this->assertNotEmpty($view->isDefinedDistanceFromSource);
        $this->assertNotEmpty($view->water);
        $this->assertNotEmpty($view->firstRecord);
        $this->assertNotEmpty($view->latestRecord);
        $this->assertNotEmpty($view->recordWithMaximumWaterLevel);
        $this->assertNotEmpty($view->recordWithMinimumWaterLevel);
        $this->assertNotEmpty($view->waterLevelDifferenceForLastDay);
        $this->assertTrue($view->hasRecordsOfWaterTemperature);
    }

    private function getUrlGenerator(): UrlGeneratorInterface
    {
        $mock = $this->createMock(UrlGeneratorInterface::class);

        $mock->method('generate')
            ->willReturn('/link-to-view');

        return $mock;
    }

    private function getGaugingStationRecordRepository(): GaugingStationRecordRepository
    {
        $mock = $this->createMock(GaugingStationRecordRepository::class);

        $mock->method('getRecordWithMaximumWaterLevelForGaugingStation')
            ->willReturn($this->createMock(GaugingStationProviderRecord::class));

        $mock->method('getRecordWithMinimumWaterLevelForGaugingStation')
            ->willReturn($this->createMock(GaugingStationProviderRecord::class));

        $mock->method('getWaterLevelDifferenceForLastDayForGaugingStation')
            ->willReturn(42);

        $mock->method('hasRecordsOfWaterTemperatureForGaugingStation')
            ->willReturn(true);

        return $mock;
    }

    private function createEntity(): GaugingStation
    {
        $water = $this->createMock(Water::class);

        $provider = $this->createMock(GaugingStationProvider::class);
        $provider->method('getExternalIdentifier')
            ->willReturn($this->createMock(ExternalIdentifier::class));

        $mock = $this->createMock(GaugingStation::class);

        $mock->method('getId')
            ->willReturn($this->createMock(UuidInterface::class));
        $mock->method('getName')
            ->willReturn('Some name');
        $mock->method('getSlug')
            ->willReturn('slug');
        $mock->method('getShortUuid')
            ->willReturn('short-uuid');
        $mock->method('isActive')
            ->willReturn(true);
        $mock->method('getGaugingStationProviders')
            ->willReturn(new ArrayCollection([$provider]));
        $mock->method('getGeographicalPosition')
            ->willReturn($this->createMock(GeographicalPosition::class));
        $mock->method('getWater')
            ->willReturn($water);
        $mock->method('getDistanceToEstuary')
            ->willReturn((float) 32);
        $mock->method('isDefinedDistanceFromSource')
            ->willReturn(true);
        $mock->method('getFirstRecord')
            ->willReturn($this->createMock(GaugingStationProviderRecord::class));
        $mock->method('getLatestRecord')
            ->willReturn($this->createMock(GaugingStationProviderRecord::class));
        $mock->method('getDistanceFromSourceOfAncestorWater')
            ->willReturn((float) 42);

        return $mock;
    }
}
