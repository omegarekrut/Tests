<?php

namespace Tests\Controller;

use Symfony\Component\HttpFoundation\Response;

/**
 * @group userbars
 * @group static-page
 */
class UserbarsControllerTest extends TestCase
{
    public function testViewPage(): void
    {
        $userbarPage = $this->getBrowser()->request('GET', '/userbars/');

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString('Рыбацкие юзербары и кнопки', $userbarPage->filter('h1')->first()->text());

        $subHeaders = $userbarPage->filter('h2');

        $this->assertCount(4, $subHeaders);

        foreach ($subHeaders as $h2DomElement) {
            $this->assertContains($h2DomElement->textContent, [
                '1. Мой водный транспорт',
                '2. Фанат Fishingsib.ru',
                '3. Личный рекорд',
                '4. Кнопка Fishingsib.ru',
            ]);
        }
    }
}
