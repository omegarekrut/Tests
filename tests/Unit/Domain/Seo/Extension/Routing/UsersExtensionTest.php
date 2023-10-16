<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Seo\Extension\Routing\UsersExtension;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use Symfony\Component\Form\FormView;
use Tests\Traits\RouteExtensionTrait;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

/**
 * @group seo
 */
class UsersExtensionTest extends TestCase
{
    use RouteExtensionTrait;

    /** @var SeoPage */
    private $seoPage;
    /** @var UsersExtension */
    private $userRouteExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seoPage = new SeoPage();

        $this->userRouteExtension = new UsersExtension($this->createBreadcrumbsFactoryMock(), $this->createUrlGeneratorMock());
    }

    public function testSeoDataForUsersChangeEmail(): void
    {
        $route = new Route('users_change_email', new Uri(''), [
            'slug' => '/users/change_emails/',
        ]);

        $this->assertTrue($this->userRouteExtension->isSupported($route));

        $this->userRouteExtension->apply($this->seoPage, new SeoContext([$route]));

        $this->assertEquals('Изменение адреса электронной почты', $this->seoPage->getTitle());
        $this->assertEquals('Изменение адреса электронной почты', $this->seoPage->getDescription());
    }

    public function testUnsupported(): void
    {
        $route = new Route('invalid_route', new Uri(''));

        $this->assertFalse($this->userRouteExtension->isSupported($route));
    }

    public function testSeoDataForUsersList(): void
    {
        $route = new Route('users_list', new Uri(''));
        $this->userRouteExtension->apply($this->seoPage, new SeoContext([$route, new FormView()]));

        $this->assertEquals('Список пользователей сайта', $this->seoPage->getTitle());
        $this->assertEquals('Список пользователей сайта', $this->seoPage->getH1());
        $this->assertEquals('Информация о пользователях сайта - профили, все записи пользователей по разделам, комментарии.', $this->seoPage->getDescription());
    }

    public function testSeoDataForUsersListWithSearchQuery(): void
    {
        $route = new Route('users_list', new Uri(''));
        $formView = new FormView();
        $formView->vars = ['data' => json_decode(json_encode(['search' => 'Карась']))];
        $this->userRouteExtension->apply($this->seoPage, new SeoContext([$route, $formView]));

        $this->assertEquals('Список пользователей сайта', $this->seoPage->getTitle());
        $this->assertEquals('Список пользователей сайта', $this->seoPage->getH1());
        $this->assertEquals('Информация о пользователях сайта - профили, все записи пользователей по разделам, комментарии.', $this->seoPage->getDescription());
        $this->assertCount(1, $this->seoPage->getBreadcrumbs());
    }

    /**
     * @dataProvider getRoutesForCheckSupports
     */
    public function testIsSupportedRoutes(string $routeName, bool $expectedIsSupported): void
    {
        $route = $this->createConfiguredMock(Route::class, [
            'getName' => $routeName,
        ]);

        $this->assertEquals($expectedIsSupported, $this->userRouteExtension->isSupported($route));
    }

    public function getRoutesForCheckSupports(): array
    {
        return [
            'users_change_email_route' => ['users_change_email', true],
            'users_list_route' => ['users_list', true],
            'users_list_pagination_route' => ['users_list_pagination', true],
            'users_list_fail_route' => ['users_list_fail_route', false],
        ];
    }
}
