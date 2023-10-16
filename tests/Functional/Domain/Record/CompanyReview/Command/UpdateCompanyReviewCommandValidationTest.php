<?php

namespace Tests\Functional\Domain\Record\CompanyReview\Command;

use App\Domain\Record\CompanyReview\Command\UpdateCompanyReviewCommand;
use App\Domain\Record\CompanyReview\Entity\CompanyReview;
use Tests\DataFixtures\ORM\Record\CompanyReview\LoadCompanyReviews;
use Tests\Functional\ValidationTestCase;

class UpdateCompanyReviewCommandValidationTest extends ValidationTestCase
{
    private UpdateCompanyReviewCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadCompanyReviews::class,
        ])->getReferenceRepository();

        $companyReview = $referenceRepository->getReference(LoadCompanyReviews::REFERENCE_NAME);
        assert($companyReview instanceof CompanyReview);

        $this->command = new UpdateCompanyReviewCommand($companyReview);
    }

    public function testNotBlankTextReview(): void
    {
        $this->command->text = '';

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('text', 'Это поле обязательно для заполнения.');
    }

    public function testNotBlankCompanyReviewId(): void
    {
        $this->command->companyReviewId = '';

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('companyReviewId', 'Это поле обязательно для заполнения.');
    }

    public function testInvalidCompanyReviewId(): void
    {
        $this->command->companyReviewId = 0;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('companyReviewId', 'Отзыв не найден.');
    }

    public function testInvalidLengthFields(): void
    {
        $this->command->text = $this->getFaker()->realText(20);

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('text', 'Минимальное кол-во символов 60.');
    }

    public function testContainTooMuchUpperCase(): void
    {
        $fakeText = mb_strtoupper($this->getFaker()->realText(200));
        $this->command->text = $fakeText;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('text', 'Записи, состоящие в большинстве из заглавных букв, запрещены.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
