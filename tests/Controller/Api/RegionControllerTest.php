<?php

namespace Tests\Controller\Api;

use App\Auth\Visitor\MaterialsRegion\MaterialsRegionInCookieStorage;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Region\Region\LoadNovosibirskRegion;

class RegionControllerTest extends TestCase
{
    public function testSaveRegionInCookie(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadNovosibirskRegion::class,
        ])->getReferenceRepository();

        $region = $referenceRepository->getReference(LoadNovosibirskRegion::REFERENCE_NAME);

        $browser = $this->getBrowser();

        $browser->xmlHttpRequest(
            'POST',
            sprintf('/api/region/change/%s/', $region->getId()),
        );

        $expectedCookie = $browser->getCookieJar()->get(MaterialsRegionInCookieStorage::COOKIE_NAME);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertEquals((string) $region->getId(), $expectedCookie->getValue());
    }

    public function testClearRegionInCookie(): void
    {
        $browser = $this->getBrowser();

        $browser->xmlHttpRequest(
            'POST',
            '/api/region/clear/',
        );

        $expectedCookie = $browser->getCookieJar()->get(MaterialsRegionInCookieStorage::COOKIE_NAME);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertEquals('region is not defined', $expectedCookie->getValue());
    }
}
