<?php

namespace Tests\Controller\Api;

use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;

class ModalSelectListControllerTest extends TestCase
{
    public function testViewPage(): void
    {
        $browser = $this->getBrowser();

        $browser->request('POST', '/api/modal-select-list/regions/');

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
    }
}
