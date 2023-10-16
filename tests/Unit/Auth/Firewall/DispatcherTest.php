<?php

namespace Tests\Unit\Auth\Firewall;

use App\Auth\Firewall\AuthenticationError\AuthenticationExceptionInSessionStorage;
use App\Auth\Firewall\Dispatcher;
use Symfony\Bundle\SecurityBundle\EventListener\FirewallListener;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Http\Firewall\ContextListener;
use Tests\Unit\TestCase;

class DispatcherTest extends TestCase
{
    public function testDispatch()
    {
        $kernel = $this->createMock(KernelInterface::class);
        $firewallListener = $this->createListenerMock(FirewallListener::class, 'onKernelRequest');
        $contextListener = $this->createListenerMock(ContextListener::class, 'onKernelResponse');
        $eventDispatcher = $this->createEventDispatchMock($firewallListener, $contextListener);
        $session = $this->createSessionMock();
        $authenticationExceptionInSessionStorage = $this->createMock(AuthenticationExceptionInSessionStorage::class);
        $request = new Request();

        $dispatcher = new Dispatcher($kernel, $eventDispatcher, $firewallListener, $contextListener, $session, $authenticationExceptionInSessionStorage);
        $dispatcher->dispatch($request);

        $this->assertEquals($session, $request->getSession());
    }

    private function createEventDispatchMock(FirewallListener $firewallListener, ContextListener $contextListener): EventDispatcher
    {
        $stub = $this
            ->getMockBuilder(EventDispatcher::class)
            ->getMock();

        $stub
            ->method('removeListener')
            ->will($this->returnValueMap([
                [KernelEvents::REQUEST, [$firewallListener, 'onKernelRequest'], null],
                [KernelEvents::RESPONSE, [$contextListener, 'onKernelResponse'], null],
                [],
            ]));

        return $stub;
    }

    private function createListenerMock(string $class, string $method)
    {
        $stub = $this->createMock($class);
        $stub
            ->method($method)
            ->willReturn(null)
        ;

        return $stub;
    }

    private function createSessionMock(): Session
    {
        $stub = $this->createMock(Session::class);

        $stub
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn(false)
        ;

        $stub
            ->expects($this->once())
            ->method('start')
            ->willReturn(null)
        ;

        return $stub;
    }
}
