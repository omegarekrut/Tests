<?php

namespace Tests\Unit\EventSubscriber;

use App\Auth\AuthService;
use App\Auth\Visitor\Visitor;
use App\Domain\Ban\Entity\BanInformationInterface;
use App\Domain\Ban\Service\BanInterface;
use App\Domain\User\Entity\User;
use App\EventSubscriber\CheckBannedUserSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\Unit\TestCase;

final class CheckBannedUserSubscriberTest extends TestCase
{
    private const EXPECTED_REDIRECT_URL = 'expected_redirect_url';

    public function testCheckMustBePassedForGuest(): void
    {
        $visitor = $this->createGuestVisitor();
        $ban = $this->createBan(false);
        $session = $this->createSession();
        $subscriber = $this->createCheckBannedUserSubscriber($visitor, $ban, $session);

        $getResponseEvent = $this->createGetResponseEvent();

        $subscriber->checkIfUserIsBanned($getResponseEvent);

        $this->assertNull($getResponseEvent->getResponse());
    }

    public function testCheckMustBePassedForNotBannedUser(): void
    {
        $user = $this->createUser();
        $visitor = $this->createUserVisitor($user);
        $ban = $this->createBan(false);
        $session = $this->createSession();
        $subscriber = $this->createCheckBannedUserSubscriber($visitor, $ban, $session);

        $getResponseEvent = $this->createGetResponseEvent();

        $subscriber->checkIfUserIsBanned($getResponseEvent);

        $this->assertNull($getResponseEvent->getResponse());
    }

    public function testCheckMustBeFailedForBannedUser(): void
    {
        $user = $this->createUser();
        $visitor = $this->createUserVisitor($user);
        $ban = $this->createBan(true);
        $session = $this->createSession();
        $subscriber = $this->createCheckBannedUserSubscriber($visitor, $ban, $session);

        $getResponseEvent = $this->createGetResponseEvent();

        $subscriber->checkIfUserIsBanned($getResponseEvent);

        $this->assertNotNull($getResponseEvent->getResponse());
        $this->assertContains('Ваша учетная запись заблокирована', $session->getFlashBag()->get('error'));
        $this->assertStringContainsString(self::EXPECTED_REDIRECT_URL, $getResponseEvent->getResponse()->getContent());
    }

    public function testCheckMustBeFailedAndExpectedEmptyRedirectResponseIfPageIsSecurityLogin(): void
    {
        $user = $this->createUser();
        $visitor = $this->createUserVisitor($user);
        $ban = $this->createBan(true);
        $session = $this->createSession();
        $subscriber = $this->createCheckBannedUserSubscriber($visitor, $ban, $session);

        $getResponseEvent = $this->createGetResponseEventWithSecurityLoginPage();

        $subscriber->checkIfUserIsBanned($getResponseEvent);

        $this->assertNull($getResponseEvent->getResponse());
        $this->assertContains('Ваша учетная запись заблокирована', $session->getFlashBag()->get('error'));
    }

    private function createUser(): User
    {
        return $this->createConfiguredMock(User::class, [
            'getId' => 1,
        ]);
    }

    private function createGuestVisitor(): Visitor
    {
        return $this->createConfiguredMock(Visitor::class, [
            'isGuest' => true,
        ]);
    }

    private function createUserVisitor(User $user): Visitor
    {
        return $this->createConfiguredMock(Visitor::class, [
            'isGuest' => false,
            'getUser' => $user,
        ]);
    }

    private function createBan(bool $hasBanInformation): BanInterface
    {
        return $this->createConfiguredMock(BanInterface::class, [
            'getBanInformationByUserId' => $hasBanInformation ? $this->createMock(BanInformationInterface::class) : null,
        ]);
    }

    private function createSession(): Session
    {
        return $this->createConfiguredMock(Session::class, [
            'getFlashBag' => new FlashBag(),
        ]);
    }

    private function createGetResponseEvent(): GetResponseEvent
    {
        return new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            Request::create('/'),
            HttpKernelInterface::MASTER_REQUEST,
        );
    }

    private function createGetResponseEventWithSecurityLoginPage(): GetResponseEvent
    {
        $request = Request::create('/');
        $request->attributes->add(['_route' => 'security_login']);

        return new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
        );
    }

    private function createCheckBannedUserSubscriber(Visitor $visitor, BanInterface $ban, Session $session): CheckBannedUserSubscriber
    {
        return new CheckBannedUserSubscriber(
            $visitor,
            $ban,
            $session,
            $this->createMock(AuthService::class),
            $this->createConfiguredMock(UrlGeneratorInterface::class, [
                'generate' => self::EXPECTED_REDIRECT_URL,
            ]),
        );
    }
}
