<?php

namespace Tests\Functional;

use App\Kernel;
use App\Service\ClientIp;
use Doctrine\ORM\EntityManager;
use Gedmo\IpTraceable\IpTraceableListener;
use League\Tactician\CommandBus;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use PHPUnit\Framework\TestResult;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Tests\DataFixtures\ORM\SingleReferenceFixtureInterface;
use Tests\Traits\FakerFactoryTrait;
use Tests\Traits\FileSystemTrait;

abstract class TestCase extends WebTestCase
{
    use FakerFactoryTrait;
    use FileSystemTrait;

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        $this->initGedmoIpTraceableListener();

        /** @todo delete after the schema update strategy appears in liip bundle */
        $this->setExcludedDoctrineTables(['fake_table_name_to_ban_the_consciousness_of_the_new_scheme_only_update']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        static::$kernel = null;
    }

    protected function getKernel(): KernelInterface
    {
        return static::$kernel;
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->getKernel()->getContainer();
    }

    protected function getEntityManager(): EntityManager
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    protected function getCommandBus(): CommandBus
    {
        return $this->getContainer()->get('tactician.commandbus.default');
    }

    protected function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->getContainer()->get('event_dispatcher');
    }

    final protected function clearDatabase(): void
    {
        $this->loadFixtures([]);
    }

    /**
     * @template T
     *
     * @param class-string<SingleReferenceFixtureInterface> $singleFixtureClass
     * @param class-string<T> $expectedReferenceClass
     *
     * @return T
     */
    final protected function loadFixture(string $singleFixtureClass, string $expectedReferenceClass): object
    {
        $referenceRepository = $this->loadFixtures([$singleFixtureClass], true)->getReferenceRepository();

        $reference = $referenceRepository->getReference($singleFixtureClass::getReferenceName());
        assert($reference instanceof $expectedReferenceClass);

        return $reference;
    }

    private function initGedmoIpTraceableListener(): void
    {
        $ipTraceableListener = $this->getContainer()->get(IpTraceableListener::class);
        $clientIp = $this->getContainer()->get(ClientIp::class);
        $ipTraceableListener->setIpValue($clientIp->getIp());
    }

    public function run(?TestResult $result = null): TestResult
    {
        $this->setPreserveGlobalState(false);

        return parent::run($result);
    }
}
