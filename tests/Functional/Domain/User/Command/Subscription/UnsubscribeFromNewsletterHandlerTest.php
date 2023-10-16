<?php

namespace Tests\Functional\Domain\User\Command\Subscription;

use App\Domain\User\Command\Subscription\Handler\UnsubscribeFromNewsletterHandler;
use App\Domain\User\Command\Subscription\UnsubscribeFromNewsletterCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Generator\SubscribeNewsletterHashGenerator;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Unit\Mock\ObjectManagerMock;
use Tests\Functional\TestCase;

class UnsubscribeFromNewsletterHandlerTest extends TestCase
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

    public function testUnsubscribeSubscribedUsers(): void
    {
        $subscribedUser = $this->user->subscribeToWeeklyNewsletter();

        $handler = new UnsubscribeFromNewsletterHandler(new ObjectManagerMock());
        $command = new UnsubscribeFromNewsletterCommand(
            $subscribedUser,
            $this->hashGenerator->generate($subscribedUser->getId())
        );

        $handler->handle($command);

        $this->assertFalse($subscribedUser->isSubscribedToWeeklyNewsletter());
    }

    public function testUnsubscribeAlreadyUnsubscribedUsers(): void
    {
        $unsubscribedUser = $this->user->unsubscribeFromWeeklyNewsletter();

        $handler = new UnsubscribeFromNewsletterHandler(new ObjectManagerMock());
        $command = new UnsubscribeFromNewsletterCommand(
            $unsubscribedUser,
            $this->hashGenerator->generate($unsubscribedUser->getId())
        );

        $handler->handle($command);

        $this->assertFalse($unsubscribedUser->isSubscribedToWeeklyNewsletter());
    }
}
