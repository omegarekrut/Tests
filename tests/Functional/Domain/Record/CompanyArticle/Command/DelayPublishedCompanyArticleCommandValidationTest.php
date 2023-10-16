<?php

namespace Tests\Functional\Domain\Record\CompanyArticle\Command;

use App\Domain\Record\CompanyArticle\Command\DelayPublishedCompanyArticleCommand;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadPaidReservoirsCompanyArticle;
use Tests\Functional\ValidationTestCase;

/**
 * @group company
 */
class DelayPublishedCompanyArticleCommandValidationTest extends ValidationTestCase
{
    private DelayPublishedCompanyArticleCommand $command;
    private CompanyArticle $companyArticle;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadPaidReservoirsCompanyArticle::class,
        ])->getReferenceRepository();

        $this->companyArticle = $referenceRepository->getReference(LoadPaidReservoirsCompanyArticle::REFERENCE_NAME);
        $this->command = new DelayPublishedCompanyArticleCommand();
    }

    public function testNotCorrectCompanyArticleId(): void
    {
        $this->command->companyArticleId = 0;
        $this->command->publishAt = Carbon::now()->addHour();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('companyArticleId', 'Такой статьи не существует.');
    }

    public function testNotCorrectPublishAt(): void
    {
        $this->command->companyArticleId = $this->companyArticle->getId();
        $this->command->publishAt = '29.08.2020';

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('publishAt', 'Тип значения должен быть datetime.');
    }

    public function testEmptyPublishAt(): void
    {
        $this->command->companyArticleId = $this->companyArticle->getId();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('publishAt', 'Это поле обязательно для заполнения.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->command->companyArticleId = $this->companyArticle->getId();
        $this->command->publishAt = Carbon::now()->addHour();

        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
