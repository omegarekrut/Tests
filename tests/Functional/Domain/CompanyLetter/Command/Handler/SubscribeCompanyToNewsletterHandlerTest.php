<?php

namespace Tests\Functional\Domain\CompanyLetter\Command\Handler;

use App\Domain\Company\Entity\Company;
use App\Domain\CompanyLetter\Command\SubscribeCompanyToNewsletterCommand;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\Functional\TestCase;

class SubscribeCompanyToNewsletterHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $company = $this->loadCompany();
        $company->unsubscribeFromNewsletter();

        $command = new SubscribeCompanyToNewsletterCommand($company);

        $this->getCommandBus()->handle($command);

        $this->assertTrue($company->isSubscribedToNewsletter());
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
