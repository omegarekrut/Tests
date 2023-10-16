<?php

namespace Tests\Functional\EventSubscriber;

use App\Auth\Visitor\Visitor;
use App\Domain\User\Entity\User;
use App\EventSubscriber\KernelRequestSubscriber;
use App\Module\ExecutionTimeDebug\ExecutionTimeViewInjector;
use App\Module\Redirect\RedirectResolver;
use App\Service\ClientIp;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Gedmo\IpTraceable\IpTraceableListener;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

class KernelRequestSubscriberTest extends TestCase
{
    private const USER_IP = '127.0.0.1';

    private ReferenceRepository $fixtureReferences;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtureReferences = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();
    }

    protected function tearDown(): void
    {
        unset($this->fixtureReferences);

        parent::tearDown();
    }

    public function testUpdateUserLastVisitInvokedByKernelRequest(): void
    {
        $user = $this->fixtureReferences->getReference(LoadTestUser::USER_TEST);
        assert($user instanceof User);

        $oldVisitedAt = $user->getLastVisit()->getLastVisitAt();

        $redirectResolver = $this->createMock(RedirectResolver::class);
        $requestSubscriber = $this->createKernelRequestSubscriber($user, $redirectResolver);
        $event = new GetResponseEvent($this->getKernel(), Request::create('/'), HttpKernelInterface::MASTER_REQUEST);

        $this->createEventDispatcher($requestSubscriber)->dispatch($event, KernelEvents::REQUEST);

        $this->assertEquals(self::USER_IP, $user->getLastVisit()->getLastVisitIp());
        $this->assertNotEquals($oldVisitedAt, $user->getLastVisit()->getLastVisitAt());
    }

    public function testRedirectUriFromConfigDontCallResolverForSubRequests(): void
    {
        $user = $this->fixtureReferences->getReference(LoadTestUser::USER_TEST);

        $redirectResolver = $this->createMock(RedirectResolver::class);
        $redirectResolver
            ->expects($this->never())
            ->method('resolve');

        $requestSubscriber = $this->createKernelRequestSubscriber($user, $redirectResolver);
        $event = new GetResponseEvent($this->getKernel(), Request::create('/'), HttpKernelInterface::SUB_REQUEST);

        $this->createEventDispatcher($requestSubscriber)->dispatch($event, KernelEvents::REQUEST);
    }

    public function testRedirectUriFromConfigWillBeRedirected(): void
    {
        $user = $this->fixtureReferences->getReference(LoadTestUser::USER_TEST);
        $expectedRedirectUri = '/redirect';

        $redirectResolver = $this->createMock(RedirectResolver::class);
        $redirectResolver
            ->expects($this->once())
            ->method('resolve')
            ->willReturn($expectedRedirectUri);

        $requestSubscriber = $this->createKernelRequestSubscriber($user, $redirectResolver);
        $event = new GetResponseEvent($this->getKernel(), Request::create('/'), HttpKernelInterface::MASTER_REQUEST);

        $this->createEventDispatcher($requestSubscriber)->dispatch($event, KernelEvents::REQUEST);

        $this->assertSame($expectedRedirectUri, $event->getResponse()->headers->get('location'));
        $this->assertSame(301, $event->getResponse()->getStatusCode());
    }

    public function testRedirectUriFromConfigWillNotBeRedirectedIfResolverReturnNull(): void
    {
        $user = $this->fixtureReferences->getReference(LoadTestUser::USER_TEST);

        $redirectResolver = $this->createMock(RedirectResolver::class);
        $redirectResolver
            ->expects($this->once())
            ->method('resolve')
            ->willReturn(null);

        $requestSubscriber = $this->createKernelRequestSubscriber($user, $redirectResolver);
        $event = new GetResponseEvent($this->getKernel(), Request::create('/'), HttpKernelInterface::MASTER_REQUEST);

        $this->createEventDispatcher($requestSubscriber)->dispatch($event, KernelEvents::REQUEST);

        $this->assertNull($event->getResponse());
    }

    private function createEventDispatcher(KernelRequestSubscriber $subscriber): EventDispatcher
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber($subscriber);

        return $eventDispatcher;
    }

    private function createKernelRequestSubscriber(User $user, RedirectResolver $redirectResolver): KernelRequestSubscriber
    {
        $ipTraceableListener = $this->getMockBuilder(IpTraceableListener::class)->getMock();
        $visitorMock = $this->getVisitorMock($user);
        $clientIpMock = $this->createMock(ClientIp::class);
        $executionTimeViewInjector = new ExecutionTimeViewInjector();
        $commandBus = $this->getCommandBus();

        return new KernelRequestSubscriber(
            $ipTraceableListener,
            $clientIpMock,
            $executionTimeViewInjector,
            $visitorMock,
            $commandBus,
            $redirectResolver,
        );
    }

    private function getVisitorMock(?User $user = null, string $ip = self::USER_IP): Visitor
    {
        return $this->createConfiguredMock(Visitor::class, [
            'getUser' => $user,
            'getIp' => $ip,
        ]);
    }
}
