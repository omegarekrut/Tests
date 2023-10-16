<?php

namespace Tests\Unit\Module\Redirect;

use App\Module\Redirect\RedirectResolver;
use Resolventa\Redirect\Exception\NotSupportRedirect;
use Resolventa\Redirect\Exception\StopPropagationException;
use Resolventa\Redirect\RedirectRuleInterface;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Throwable;

class RedirectResolverTest extends TestCase
{
    private const TEST_URI = 'http://test.com/test';

    public function testResolveReturnNullForNotSupportedRedirect(): void
    {
        $resolver = new RedirectResolver();
        $resolver->addRedirectRule($this->getRedirectRuleMockWhichThrowException(new NotSupportRedirect()));

        $redirectUri = $resolver->resolve(new Uri(self::TEST_URI));

        $this->assertNull($redirectUri);
    }

    public function testResolveWorkCorrectForSupportedRedirect(): void
    {
        $expectedRedirectUri = '/redirect';
        $resolver = new RedirectResolver();
        $resolver->addRedirectRule($this->getRedirectRuleMock(new Uri($expectedRedirectUri)));

        $redirectUri = $resolver->resolve(new Uri(self::TEST_URI));

        $this->assertEquals($expectedRedirectUri, $redirectUri);
    }

    public function testResolveCanThrowPropagationException(): void
    {
        $resolver = new RedirectResolver();
        $resolver->addRedirectRule($this->getRedirectRuleMockWhichThrowException(new StopPropagationException()));
        $resolver->addRedirectRule($this->getRedirectRuleMock(null));

        $redirectUri = $resolver->resolve(new Uri(self::TEST_URI));

        $this->assertNull($redirectUri);
    }

    private function getRedirectRuleMock(?Uri $returnUri = null): RedirectRuleInterface
    {
        $redirectRuleMock = $this->createMock(RedirectRuleInterface::class);

        if ($returnUri === null) {
            $redirectRuleMock
                ->expects($this->never())
                ->method('apply');
        } else {
            $redirectRuleMock
                ->method('apply')
                ->willReturn($returnUri);
        }

        return $redirectRuleMock;
    }

    private function getRedirectRuleMockWhichThrowException(Throwable $exception): RedirectRuleInterface
    {
        $redirectRuleMock = $this->createMock(RedirectRuleInterface::class);

        $redirectRuleMock
            ->method('apply')
            ->willThrowException($exception);

        return $redirectRuleMock;
    }
}
