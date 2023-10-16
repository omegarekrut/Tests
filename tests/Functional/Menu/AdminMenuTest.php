<?php

namespace Tests\Functional\Menu;

use App\Menu\AdminMenu;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuItem;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tests\Functional\TestCase;

class AdminMenuTest extends TestCase
{
    public function testMainMenu(): void
    {
        $adminMenu = new AdminMenu();
        $adminMenu->setContainer($this->getContainerMock());

        $menu = $adminMenu->mainMenu($this->getContainer()->get('knp_menu.factory'), [
            'rootClass' => 'abc',
        ]);

        $this->assertArrayHasKey('class', $menu->getChildrenAttributes());

        /**
         * @var MenuItem[]
         */
        $children = $menu->getChildren();

        if (count($children) == 0) {
            return;
        }

        $firstChild = array_pop($children);
        $this->assertArrayHasKey('class', $firstChild->getLinkAttributes());
        $this->assertNotNull($firstChild->getName());
    }

    public function testHideItems(): void
    {
        $adminMenu = new AdminMenu();
        $adminMenu->setContainer($this->getContainerMock([
            'admin/index',
        ]));

        $menu = $adminMenu->mainMenu($this->getContainer()->get('knp_menu.factory'), [
            'rootClass' => 'abc',
        ]);

        $mainPageItem = $menu->getChild('Главная страница');
        $banItem = $menu->getChild('Баны');

        $this->assertEmpty($mainPageItem);
        $this->assertInstanceOf(ItemInterface::class, $banItem);
    }

    private function getContainerMock($denyResources = []): ContainerBuilder
    {
        $authChecker = new class($denyResources) {
            private $denyResources;

            public function __construct($denyResources)
            {
                $this->denyResources = $denyResources;
            }

            public function isGranted($resource) {
                if (in_array($resource, $this->denyResources)) {
                    return false;
                }

                return true;
            }
        };

        $container = new ContainerBuilder();
        $container->set('security.authorization_checker', $authChecker);
        $container->set('router', $this->getContainer()->get('router'));

        return $container;
    }
}
