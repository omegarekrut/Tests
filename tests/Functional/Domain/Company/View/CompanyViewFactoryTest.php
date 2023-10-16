<?php

namespace Tests\Functional\Domain\Company\View;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\View\CompanyViewFactory;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithoutOwner;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithSubscriber;
use Tests\DataFixtures\ORM\User\LoadUserWithSubscriptionOnCompany;
use Tests\Functional\TestCase;

class CompanyViewFactoryTest extends TestCase
{
    public function testHasOwnerFalseWithCompanyNoOwner(): void
    {
        $referenceRepository = $this->loadFixtures([LoadCompanyWithoutOwner::class])->getReferenceRepository();
        $company = $referenceRepository->getReference(LoadCompanyWithoutOwner::REFERENCE_NAME);
        assert($company instanceof Company);

        $companyViewFactory = $this->getContainer()->get(CompanyViewFactory::class);

        $companyView = $companyViewFactory->create($company);

        $this->assertFalse($companyView->hasOwner);
    }

    public function testHasOwnerTrueWithCompanyOwner(): void
    {
        $referenceRepository = $this->loadFixtures([LoadCompanyWithOwner::class])->getReferenceRepository();
        $company = $referenceRepository->getReference(LoadCompanyWithOwner::REFERENCE_NAME);
        assert($company instanceof Company);

        $companyViewFactory = $this->getContainer()->get(CompanyViewFactory::class);

        $companyView = $companyViewFactory->create($company);

        $this->assertTrue($companyView->hasOwner);
    }

    public function testHasGalleryAndYoutubeVideo(): void
    {
        $referenceRepository = $this->loadFixtures([LoadAquaMotorcycleShopsCompany::class])->getReferenceRepository();
        $company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        assert($company instanceof Company);

        $companyViewFactory = $this->getContainer()->get(CompanyViewFactory::class);

        $companyView = $companyViewFactory->create($company);

        $this->assertNotEmpty($companyView->galleryView);
        $this->assertNotEmpty($companyView->videoUrls);
    }

    public function testCompanyIsActual(): void
    {
        $referenceRepository = $this->loadFixtures([LoadAquaMotorcycleShopsCompany::class])->getReferenceRepository();
        $company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        assert($company instanceof Company);

        $companyViewFactory = $this->getContainer()->get(CompanyViewFactory::class);

        $companyView = $companyViewFactory->create($company);

        $this->assertTrue($companyView->isActual);
    }

    public function testCompanyIsNotActual(): void
    {
        $referenceRepository = $this->loadFixtures([LoadAquaMotorcycleShopsCompany::class])->getReferenceRepository();
        $company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        assert($company instanceof Company);

        $companyViewFactory = $this->getContainer()->get(CompanyViewFactory::class);

        Carbon::setTestNow(Carbon::now()->subYear());
        $companyView = $companyViewFactory->create($company);

        $this->assertFalse($companyView->isActual);
        Carbon::setTestNow();
    }

    public function testCompanyWithSubscribers(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadUserWithSubscriptionOnCompany::class,
            LoadCompanyWithSubscriber::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadCompanyWithSubscriber::REFERENCE_NAME);
        assert($company instanceof Company);

        $companyViewFactory = $this->getContainer()->get(CompanyViewFactory::class);

        Carbon::setTestNow(Carbon::now()->subYear());
        $companyView = $companyViewFactory->create($company);

        $this->assertEquals(1, $companyView->subscriberCount);
        Carbon::setTestNow();
    }
    public function testCompanyDescriptionHaveTargetBlankAttributeAfterCreating(): void
    {
        $referenceRepository = $this->loadFixtures([LoadCompanyWithOwner::class])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadCompanyWithOwner::REFERENCE_NAME);
        assert($company instanceof Company);

        $company->updateDescription('[url=https://www.fishingsib.ru/]fishingsib[/url]');

        $companyViewFactory = $this->getContainer()->get(CompanyViewFactory::class);

        $companyView = $companyViewFactory->create($company);

        $this->assertEquals('<a href="https://www.fishingsib.ru/" target="_blank" rel="nofollow">fishingsib</a>', $companyView->description);
    }

    public function testCompanyDescriptionWhichHaveFakeLinkHaveNotTargetBlankAttributeAfterCreating(): void
    {
        $referenceRepository = $this->loadFixtures([LoadCompanyWithOwner::class])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadCompanyWithOwner::REFERENCE_NAME);
        assert($company instanceof Company);

        $company->updateDescription('[url=fake_link]Lorem ipsum[/url]');

        $companyViewFactory = $this->getContainer()->get(CompanyViewFactory::class);

        $companyView = $companyViewFactory->create($company);

        $this->assertEquals('Lorem ipsum', $companyView->description);
    }
}
