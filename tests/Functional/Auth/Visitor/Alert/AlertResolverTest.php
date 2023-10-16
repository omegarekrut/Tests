<?php

namespace Tests\Functional\Auth\Visitor\Alert;

use App\Auth\Firewall\AuthenticationError\AuthenticationExceptionInSessionStorage;
use App\Auth\Visitor\Alert\AlertResolver;
use App\Module\Alert\ValueObject\AlertDuration;
use App\Module\Alert\ValueObject\AlertLevel;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Tests\Functional\TestCase;

/**
 * @group alert
 */
class AlertResolverTest extends TestCase
{
    /** @var AlertResolver */
    private $alertResolver;
    /** @var FlashBagInterface */
    private $flashBag;
    /** @var AuthenticationExceptionInSessionStorage */
    private $authenticationExceptionStorage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->alertResolver = $this->getContainer()->get(AlertResolver::class);
        $this->flashBag = $this->getContainer()->get('session')->getFlashBag();
        $this->authenticationExceptionStorage = $this->getContainer()->get(AuthenticationExceptionInSessionStorage::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->alertResolver,
            $this->flashBag,
            $this->authenticationExceptionStorage
        );

        parent::tearDown();
    }

    public function testAlertShouldBeResolvedFromFlashMessages(): void
    {
        $expectedMessage = 'expected message';
        $this->flashBag->add('success', $expectedMessage);

        $alerts = $this->alertResolver->resolveAlerts();

        $this->assertCount(1, $alerts);

        $actualAlert = $alerts->first();

        $this->assertEquals($expectedMessage, $actualAlert->getMessage());
        $this->assertTrue(AlertLevel::success()->equals($actualAlert->getLevel()));
        $this->assertTrue(AlertDuration::short()->equals($actualAlert->getDuration()));
    }

    public function testAlertShouldBeResolvedFromAuthException(): void
    {
        $this->authenticationExceptionStorage->setException(new BadCredentialsException());

        $alerts = $this->alertResolver->resolveAlerts();

        $this->assertCount(1, $alerts);

        $actualAlert = $alerts->first();

        $this->assertEquals('Неверный логин или пароль.', $actualAlert->getMessage());
        $this->assertTrue(AlertLevel::error()->equals($actualAlert->getLevel()));
        $this->assertTrue(AlertDuration::permanent()->equals($actualAlert->getDuration()));
    }

    public function testAlertsShouldNotAppearJustLikeThat(): void
    {
        $alerts = $this->alertResolver->resolveAlerts();

        $this->assertCount(0, $alerts);
    }
}
