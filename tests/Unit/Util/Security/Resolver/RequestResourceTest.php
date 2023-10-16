<?php

namespace Tests\Unit\Util\Security\Resolver;

use App\Util\Security\Resolver\RequestResource;
use Symfony\Component\HttpFoundation\Request;
use Tests\Unit\TestCase;

class RequestResourceTest extends TestCase
{
    private $request;
    private $requestResourceResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = Request::createFromGlobals();
        $this->requestResourceResolver = new RequestResource([
            'App',
        ]);
    }

    /**
     * @dataProvider getAttributeControllerAndResolvedResource
     */
    public function testResolveResource(string $attributeController, string $resolvedResource)
    {
        $this->request->attributes->set('_controller', $attributeController);
        $resource = $this->requestResourceResolver->getResource($this->request);

        $this->assertEquals($resolvedResource, $resource);
    }

    public function getAttributeControllerAndResolvedResource(): array
    {
        return [
            'from service line' => [
                'app.controller.PagesController:show',
                'pages:show',
            ],
            'from controller instance line' => [
                'App\Page\PagesController::show',
                'page_pages:show',
            ],
            'action with postfix (symfony action postfix)' => [
                'App\Page\PagesController::showAction',
                'page_pages:show',
            ],
            'action with prefix (cake admin action prefix)' => [
                'App\Page\PagesController::admin_show',
                'admin/page_pages:show',
            ],
            'action name in camel case' => [
                'App\Page\PagesController::showSomeAction',
                'page_pages:show_some',
            ],
            'controller admin namespace' => [
                'App\Admin\PagesController::showAction',
                'admin/pages:show',
            ],
            'controller name in camel case' => [
                'app.controller.SuperPagesController::show',
                'super_pages:show',
            ],
            'another service name' => [
                'another.service.name:showAction',
                'another.service.name:show'
            ],
            'controller admin namespace sub level' => [
                'App\Admin\Some\Sub\Level\NameSpace\PageController::showAction',
                'admin/some_sub_level_name_space_page:show',
            ],
        ];
    }

    public function testInvalidControllerAttribute()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Неверный формат значения атрибута _controller');

        $this->request->attributes->set('_controller', 'PagesController');
        $this->requestResourceResolver->getResource($this->request);
    }

    public function testEmptyControllerAttribute()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Атрибут _controller обязательно должен быть заполнен');

        $this->requestResourceResolver->getResource($this->request);
    }
}
