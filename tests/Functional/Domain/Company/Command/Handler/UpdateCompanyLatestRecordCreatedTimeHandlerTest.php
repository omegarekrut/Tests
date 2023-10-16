<?php

namespace Tests\Functional\Domain\Company\Command\Handler;

use App\Domain\Company\Command\UpdateCompanyLatestRecordCreatedTimeCommand;
use App\Domain\Company\Entity\Company;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadAquaMotorcycleShopsCompanyArticle;
use Tests\Functional\TestCase;
use function assert;

class UpdateCompanyLatestRecordCreatedTimeHandlerTest extends TestCase
{
    private Company $company;
    private CompanyArticle $article;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clearDatabase();

        $this->article = $this->loadFixture(LoadAquaMotorcycleShopsCompanyArticle::class, CompanyArticle::class);
        $this->company = $this->article->getCompanyAuthor();
    }

    public function testHandle(): void
    {
        $command = new UpdateCompanyLatestRecordCreatedTimeCommand($this->company);
        $this->getCommandBus()->handle($command);

        $companyRepository = $this->getEntityManager()->getRepository(Company::class);
        $company = $companyRepository->find($command->getCompany()->getId());

        assert($company instanceof Company);
        $this->assertEquals($this->article->getCreatedAt()->getTimestamp(), $company->getLastRecordCreatedAt()->getTimestamp());
    }
}
