<?php

namespace Tests\Functional\Domain\User\Command\Rating\Handler;

use App\Domain\User\Command\Rating\Handler\RecalculateUserGlobalRatingHandler;
use App\Domain\User\Command\Rating\RecalculateUserGlobalRatingCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use Tests\DataFixtures\ORM\User\LoadUserWithoutRecords;
use Tests\Functional\Mock\UserRatingCalculatorMock;
use Tests\Functional\TestCase;

/**
 * @group user
 * @group rating
 */
class RecalculateUserGlobalRatingHandlerTest extends TestCase
{
    private const EXPECTED_USER_RATING = 101;

    private User $user;
    private RecalculateUserGlobalRatingHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithoutRecords::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadUserWithoutRecords::REFERENCE_NAME);
        assert($this->user instanceof User);

        $userRatingCalculator = new UserRatingCalculatorMock();
        $userRatingCalculator->setReturnedRatingForAllPeriod(self::EXPECTED_USER_RATING);

        $this->handler = new RecalculateUserGlobalRatingHandler(
            $this->getContainer()->get(UserRepository::class),
            $userRatingCalculator
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

    public function testUpdateUserRating(): void
    {
        $recalculateUserGlobalRatingCommand = new RecalculateUserGlobalRatingCommand();
        $recalculateUserGlobalRatingCommand->userId = $this->user->getId();

        $this->assertEquals(0, $this->user->getGlobalRating()->getValue());

        $this->handler->handle($recalculateUserGlobalRatingCommand);

        $this->assertEquals(self::EXPECTED_USER_RATING, $this->user->getGlobalRating()->getValue());
    }
}
