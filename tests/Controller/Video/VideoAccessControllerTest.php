<?php

namespace Tests\Controller\Video;

use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\LoadCategories;

class VideoAccessControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([
            LoadCategories::class,
        ])->getReferenceRepository();
    }

    public function testAllowOnRegionPageForGuest(): void
    {
        $client = $this->getBrowser();
        $url = '/video/by-region/';

        $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testAllowOnAjaxRegionPageForGuest(): void
    {
        $client = $this->getBrowser();
        $url = '/video/ajax-by-region/';

        $client->xmlHttpRequest('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
    }
}
