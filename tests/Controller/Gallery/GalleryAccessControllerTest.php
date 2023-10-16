<?php

namespace Tests\Controller\Gallery;

use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\LoadCategories;

class GalleryAccessControllerTest extends TestCase
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
        $url = '/gallery/by-region/';

        $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testAllowOnAjaxRegionPageForGuest(): void
    {
        $client = $this->getBrowser();
        $url = '/gallery/ajax-by-region/';

        $client->xmlHttpRequest('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
    }
}
