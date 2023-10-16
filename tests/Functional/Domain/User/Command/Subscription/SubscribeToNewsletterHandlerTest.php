<?php

namespace Tests\Functional\Domain\User\Command\Subscription;

use App\Domain\User\Command\Subscription\Handler\SubscribeToNewsletterHandler;
use App\Domain\User\Command\Subscription\SubscribeToNewsletterCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Generator\SubscribeNewsletterHashGenerator;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Unit\Mock\ObjectManagerMock;
use Tests\Functional\TestCase;

class SubscribeToNewsletterHandlerTest extends TestCase
{
    /** @var SubscribeNewsletterHashGenerator */
    private $hashGenerator;
    /** @var User */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        $this->hashGenerator = $this->getContainer()->get(SubscribeNewsletterHashGenerator::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->user,
            $this->hashGenerator
        );

        parent::tearDown();
    }

    public function testSubscribeUnsubscribedUsers(): void
    {
        $unsubscribedUser = $this->user->unsubscribeFromWeeklyNewsletter();

        $handler = new SubscribeToNewsletterHandler(new ObjectManagerMock());
        $command = new SubscribeToNewsletterCommand(
            $unsubscribedUser,
            $this->hashGenerator->generate($unsubscribedUser->getId())
        );

        $handler->handle($command);

        $this->assertTrue($unsubscribedUser->isSubscribedToWeeklyNewsletter());
    }

    public function testSubscribeAlreadySubscribedUsers(): void
    {
        $subscribedUser = $this->user->subscribeToWeeklyNewsletter();

        $handler = new SubscribeToNewsletterHandler(new ObjectManagerMock());
        $command = new SubscribeToNewsletterCommand(
            $subscribedUser,
            $this->hashGenerator->generate($subscribedUser->getId())
        );

        $handler->handle($command);

        $this->assertTrue($subscribedUser->isSubscribedToWeeklyNewsletter());
    }
}
