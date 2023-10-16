<?php

namespace Tests\Unit\Util\Security\Resolver;

use App\Util\Security\Resolver\RequestArgument as RequestArgumentResolver;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerArgumentsEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Tests\Unit\TestCase;

class RequestArgumentTest extends TestCase
{
    public function testResolveArguments()
    {
        $expectedRequest = new Request();
        $expectedController = [$this, 'testResolveArguments'];
        $expectedArgumentTypes = [
            new \stdClass(),
            new \stdClass(),
        ];
        $expectedArgumentsResolved = [
            new \stdClass(),
            'string'
        ];
        $expectedHttpKernel = $this->createHttpKernel();

        $requestArgumentResolver = new RequestArgumentResolver(
            $this->createControllerResolver($expectedRequest, $expectedController),
            $this->createArgumentResolver($expectedRequest, $expectedController, $expectedArgumentTypes),
            $expectedHttpKernel,
            $this->createMock(ControllerListener::class),
            $this->createParamConverterListener(
                $expectedArgumentsResolved,
                $expectedHttpKernel,
                $expectedController,
                $expectedArgumentTypes,
                $expectedRequest
            )
        );

        $actualArguments = $requestArgumentResolver->getArguments($expectedRequest);

        $this->assertCount(1, $actualArguments);
        $this->assertEquals($expectedArgumentsResolved[0], $actualArguments[0]);
    }

    private function createControllerResolver(Request $expectedRequest, callable $controller): ControllerResolverInterface
    {
        $stub = $this->createMock(ControllerResolverInterface::class);
        $stub
            ->expects($this->once())
            ->method('getController')
            ->with($expectedRequest)
            ->willReturn($controller)
        ;

        return $stub;
    }

    private function createArgumentResolver(Request $expectedRequest, callable $expectedController, array $argumentTypes): ArgumentResolverInterface
    {
        $stub = $this->createMock(ArgumentResolverInterface::class);
        $stub
            ->expects($this->once())
            ->method('getArguments')
            ->with($expectedRequest, $expectedController)
            ->willReturn($argumentTypes)
        ;

        return $stub;
    }

    private function createHttpKernel(): HttpKernelInterface
    {
        return $this->createMock(HttpKernelInterface::class);
    }

    private function createParamConverterListener(
        array $resolvedArguments,
        HttpKernelInterface $expectedHttpKernel,
        callable $expectedController,
        array $expectedArgumentTypes,
        Request $expectedRequest
    ): ParamConverterListener
    {
        $stub = $this->createMock(ParamConverterListener::class);
        $stub
            ->expects($this->once())
            ->method('onKernelController')
            ->willReturnCallback(function (FilterControllerArgumentsEvent $event) use ($resolvedArguments, $expectedHttpKernel, $expectedController, $expectedArgumentTypes, $expectedRequest): void {
                $this->assertEquals($expectedHttpKernel, $event->getKernel());
                $this->assertEquals($expectedController, $event->getController());
                $this->assertEquals($expectedArgumentTypes, $event->getArguments());
                $this->assertEquals($expectedRequest, $event->getRequest());

                $expectedRequest->attributes->add($resolvedArguments);
            })
        ;

        return $stub;
    }
}
