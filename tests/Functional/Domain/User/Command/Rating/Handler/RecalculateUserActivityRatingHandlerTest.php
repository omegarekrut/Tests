<?php

namespace Tests\Functional\Domain\User\Command\Rating\Handler;

use App\Domain\User\Command\Rating\Handler\RecalculateUserActivityRatingHandler;
use App\Domain\User\Command\Rating\RecalculateUserActivityRatingCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\User\LoadUserWithoutRecords;
use Tests\Functional\Mock\UserRatingCalculatorMock;
use Tests\Functional\TestCase;

/**
 * @group user
 * @group rating
 */
class RecalculateUserActivityRatingHandlerTest extends TestCase
{
    private const EXPECTED_USER_RATING = 101;

    private User $user;
    private RecalculateUserActivityRatingHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadUserWithoutRecords::class,
        ])->getReferenceRepository();

        $this->user = $this->referenceRepository->getReference(LoadUserWithoutRecords::REFERENCE_NAME);
        assert($this->user instanceof User);

        $this->userRatingCalculator = new UserRatingCalculatorMock();
        $this->userRatingCalculator->setReturnedRatingForPeriod(self::EXPECTED_USER_RATING);

        $this->handler = new RecalculateUserActivityRatingHandler(
            $this->getContainer()->get(UserRepository::class),
            $this->userRatingCalculator
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->user,
            $this->handler,
            $this->referenceRepository,
            $this->userRatingCalculator,
        );

        parent::tearDown();
    }

    public function testUpdateUserRating(): void
    {
        $recalculateUserActivityRatingCommand = new RecalculateUserActivityRatingCommand();
        $recalculateUserActivityRatingCommand->userId = $this->user->getId();

        $this->assertEquals(0, $this->user->getActivityRating()->getValue());

        $this->handler->handle($recalculateUserActivityRatingCommand);

        $this->assertEquals(self::EXPECTED_USER_RATING, $this->user->getActivityRating()->getValue());

        $handledPeriod = $this->userRatingCalculator->getLastCalculatedPeriod();

        $this->assertNotEmpty($handledPeriod);
        $this->assertEquals(Carbon::today()->firstOfYear()->getTimestamp(), $handledPeriod->getStartDate()->getTimestamp());
        $this->assertEquals(Carbon::tomorrow()->getTimestamp(), $handledPeriod->getEndDate()->getTimestamp());
    }
}
