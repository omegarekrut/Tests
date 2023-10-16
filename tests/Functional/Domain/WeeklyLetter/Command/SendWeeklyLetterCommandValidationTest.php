<?php

namespace Tests\Functional\Domain\WeeklyLetter\Command;

use App\Domain\WeeklyLetter\Command\SendWeeklyLetterCommand;
use App\Domain\WeeklyLetter\Entity\WeeklyLetter;
use Tests\DataFixtures\ORM\WeeklyLetter\LoadWeeklyLetterBefore;
use Tests\DataFixtures\ORM\WeeklyLetter\LoadWeeklyLetterCurrent;
use Tests\Functional\ValidationTestCase;

/**
 * @group weekly-letter
 */
class SendWeeklyLetterCommandValidationTest extends ValidationTestCase
{
    /** @var WeeklyLetter */
    private $alreadySentWeeklyLetter;
    /** @var WeeklyLetter */
    private $notSentWeeklyLetter;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadWeeklyLetterBefore::class,
            LoadWeeklyLetterCurrent::class,
        ])->getReferenceRepository();

        $this->alreadySentWeeklyLetter = $referenceRepository->getReference(LoadWeeklyLetterBefore::REFERENCE_NAME);
        $this->notSentWeeklyLetter = $referenceRepository->getReference(LoadWeeklyLetterCurrent::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset($this->alreadySentWeeklyLetter);

        parent::tearDown();
    }

    public function testIsWeeklyLetterSentAlready(): void
    {
        $sendCommand = new SendWeeklyLetterCommand($this->alreadySentWeeklyLetter);

        $this->getValidator()->validate($sendCommand);

        $this->assertFieldInvalid('weeklyLetterSentAlready', 'Рассылка отправлена ранее');
    }

    public function testValidationShouldBePassedForNotSentWeeklyLetter(): void
    {
        $sendCommand = new SendWeeklyLetterCommand($this->notSentWeeklyLetter);

        $this->getValidator()->validate($sendCommand);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
