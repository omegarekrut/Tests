<?php

namespace Tests\Unit\Util\Acl;

use App\Util\Acl\CombinedAssertion;
use App\Util\Acl\DependencyInjection\AclExtension;
use App\Util\Acl\DependencyInjection\CompilerPass;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Tests\Unit\TestCase;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Resource\GenericResource as Resource;
use Laminas\Permissions\Acl\Role\GenericRole as Role;

class DependencyInjectionTest extends TestCase
{
    protected function getContainerWithExtension(bool $compile = true): ContainerInterface
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new AclExtension());
        $container->addCompilerPass(new CompilerPass());

        $loader = new YamlFileLoader($container, new FileLocator($this->getDataFixturesFolder()));
        $loader->load('aclConfiguration.yaml');

        $container->register('security.role_hierarchy', RoleHierarchy::class)->setSynthetic(true);
        $container->set('security.role_hierarchy', new RoleHierarchy([]));

        if ($compile === true) {
            $container->compile();
        }

        return $container;
    }

    public function testEquivalentManualConfiguration()
    {
        $assertionTrue = new Assertion(true);
        $assertionFalse = new Assertion(false);
        $combinedAssertion = $this->createCombinedAssertionByAssertions([
            new Assertion(true),
            new Assertion(false),
        ]);

        $expectedAcl = new Acl();
        $expectedAcl
            ->addRole(new Role('ROLE_USER'))
            ->addRole(new Role('ROLE_ADVANCED_USER'), $expectedAcl->getRole('ROLE_USER'))
            ->addRole(new Role('ROLE_ADMIN'))
            ->addRole(new Role('IS_AUTHENTICATED_ANONYMOUSLY'))

            ->addResource(new Resource('page'))
            ->addResource(new Resource('cant_trash_page'), $expectedAcl->getResource('page'))

            ->allow('ROLE_ADMIN')
            ->deny('ROLE_ADMIN', 'page', 'part_view', $assertionTrue)
            ->allow('ROLE_USER', 'page', 'view')
            ->allow('ROLE_USER', 'page', 'delete')
            ->deny('ROLE_USER', 'cant_trash_page', 'delete')
            ->deny('IS_AUTHENTICATED_ANONYMOUSLY')
            ->allow('IS_AUTHENTICATED_ANONYMOUSLY', 'page', 'view')
            ->allow('IS_AUTHENTICATED_ANONYMOUSLY', 'page', 'part_view', $assertionFalse)
            ->allow('IS_AUTHENTICATED_ANONYMOUSLY', 'page', 'ajax_view', $combinedAssertion)
        ;

        $acl = $this->getContainerWithExtension()->get('module.acl.acl');
        $this->assertEquals($expectedAcl, $acl);
    }

    private function createCombinedAssertionByAssertions(array $assertions)
    {
        $combinedAssertion = new CombinedAssertion(CombinedAssertion::STRATEGY_AT_LEAST_ONE);

        foreach ($assertions as $assertion) {
            $combinedAssertion->addAssertion($assertion);
        }

        return $combinedAssertion;
    }

    public function testConfigurationProhibition()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Иерархия ролей должна быть сконфигурирована через расширение Acl');

        $container = $this->getContainerWithExtension(false);

        $roleHierarchy = [
            'ROLE_USER' => [
                'ROLE_ADMIN',
            ],
            'ROLE_ADMIN' => [],
        ];

        $container->setParameter('security.role_hierarchy.roles', $roleHierarchy);
        $container->compile();
    }

    public function testRoleHierarchyConfiguration()
    {
        $container = $this->getContainerWithExtension();
        $roleHierarchyExpected = [
            'ROLE_USER' => [
                'ROLE_ADVANCED_USER',
            ],
            'ROLE_ADMIN' => [],
        ];

        $roleHierarchy = $container->getParameter('security.role_hierarchy.roles');
        $this->assertEquals($roleHierarchyExpected, $roleHierarchy);
    }
}
