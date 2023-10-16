<?php

namespace Tests\Functional\Domain\User\Command\Rating\Handler;

use App\Domain\User\Command\Rating\Handler\ResetUserActivityRatingHandler;
use App\Domain\User\Command\Rating\ResetUserActivityRatingCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use Tests\DataFixtures\ORM\User\LoadUserWithoutRecords;
use Tests\Functional\TestCase;

class ResetUserActivityRatingHandlerTest extends TestCase
{
    private const EXPECTED_USER_RATING = 101;

    private User $user;
    private ResetUserActivityRatingHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $userRepository = $this->getContainer()->get(UserRepository::class);

        $this->referenceRepository = $this->loadFixtures([
            LoadUserWithoutRecords::class,
        ])->getReferenceRepository();

        $this->user = $this->referenceRepository->getReference(LoadUserWithoutRecords::REFERENCE_NAME);
        assert($this->user instanceof User);

        $this->user->updateActivityRating(self::EXPECTED_USER_RATING);
        $userRepository->save($this->user);

        $this->handler = new  ResetUserActivityRatingHandler(
            $userRepository
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->user,
            $this->handler,
        );

        parent::tearDown();
    }

    public function testResetUserActivityRating(): void
    {
        $resetUserActivityRatingCommand = new ResetUserActivityRatingCommand();
        $resetUserActivityRatingCommand->user = $this->user;

        $this->assertEquals(self::EXPECTED_USER_RATING, $this->user->getActivityRating()->getValue());

        $this->handler->handle($resetUserActivityRatingCommand);

        $this->assertEquals(0, $this->user->getActivityRating()->getValue());
    }
}
