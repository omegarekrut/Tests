<?php

namespace Tests\Controller\Tidings;

use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;

class TidingsAccessControllerTest extends TestCase
{
    public function testAllowOnRegionPageForGuest(): void
    {
        $client = $this->getBrowser();
        $url = '/tidings/by-region/';

        $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testAllowOnAjaxRegionPageForGuest(): void
    {
        $client = $this->getBrowser();
        $url = '/tidings/ajax-by-region/';

        $client->xmlHttpRequest('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
    }
}
