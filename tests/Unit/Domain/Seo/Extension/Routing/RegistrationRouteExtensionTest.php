<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Seo\Extension\Routing\RegistrationRouteExtension;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

/**
 * @group seo
 */
class RegistrationRouteExtensionTest extends TestCase
{
    public function testSupportedRouteForRegistrationPage(): void
    {
        $route = new Route('user_registration', new Uri(''));
        $seoPage = new SeoPage();
        $registrationRouteExtension = new RegistrationRouteExtension();

        $this->assertTrue($registrationRouteExtension->isSupported($route));

        $registrationRouteExtension->apply($seoPage, new SeoContext([$route]));

        $this->assertEquals('Регистрация нового пользователя', $seoPage->getTitle());
        $this->assertEquals('Регистрация на сайте', $seoPage->getH1());
        $this->assertStringContainsString('Зарегистрируйтесь на сайте FishingSib.ru', $seoPage->getDescription());
    }

    public function testSupportedRouteForRequestConfirmationPage(): void
    {
        $route = new Route('request_confirmation', new Uri(''));
        $seoPage = new SeoPage();
        $registrationRouteExtension = new RegistrationRouteExtension();

        $this->assertTrue($registrationRouteExtension->isSupported($route));

        $registrationRouteExtension->apply($seoPage, new SeoContext([$route]));

        $this->assertEquals('Запрос на отправку письма для подтверждения email', $seoPage->getTitle());
        $this->assertEquals('Запрос на отправку письма для подтверждения email', $seoPage->getH1());
    }

    public function testUnsupported(): void
    {
        $route = new Route('invalid_route', new Uri(''));
        $registrationRouteExtension = new RegistrationRouteExtension();

        $this->assertFalse($registrationRouteExtension->isSupported($route));
    }
}
