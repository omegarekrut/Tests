<?php

namespace Tests\Functional\Domain\CompanyLetter\Command\Handler;

use App\Domain\Company\Entity\Company;
use App\Domain\CompanyLetter\Command\UnsubscribeCompanyFromNewsletterCommand;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\Functional\TestCase;

class UnsubscribeCompanyFromNewsletterHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $company = $this->loadCompany();
        $company->subscribeToNewsletter();

        $command = new UnsubscribeCompanyFromNewsletterCommand($company);

        $this->getCommandBus()->handle($command);

        $this->assertFalse($company->isSubscribedToNewsletter());
    }

    private function loadCompany(): Company
    {
        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompany::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        assert($company instanceof Company);

        return $company;
    }
}
