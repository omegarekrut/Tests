<?php

namespace Tests\Functional\Domain\Company\View;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\View\CompanyProfileCompletedViewFactory;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithoutOwner;
use Tests\DataFixtures\ORM\Company\Company\LoadOldCompany;
use Tests\Functional\TestCase;

class CompanyProfileCompletedViewFactoryTest extends TestCase
{
    public function testFilledCompanyProfileCompleteView(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompany::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        assert($company instanceof Company);

        $companyViewFactory = $this->getContainer()->get(CompanyProfileCompletedViewFactory::class);
        $companyView = $companyViewFactory->create($company);

        $this->assertEquals(null, $companyView->bgClass);
        $this->assertEquals(null, $companyView->message);
        $this->assertEquals(null, $companyView->header);
        $this->assertEquals(null, $companyView->daysNotUpdated);
        $this->assertEquals(60, $companyView->percentageOfCompleted);
        $this->assertEquals(null, $companyView->text);
    }

    public function testOldCompanyProfileCompleteView(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadOldCompany::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadOldCompany::REFERENCE_NAME);
        assert($company instanceof Company);

        $companyViewFactory = $this->getContainer()->get(CompanyProfileCompletedViewFactory::class);
        $companyView = $companyViewFactory->create($company);

        $this->assertEquals('Вы обновляли страницу более 6 месяцев назад.', $companyView->header);
        $this->assertEquals('Вы очень давно обновляли данные о своей компании. Убедитесь, пожалуйста, что все данные на вашей странице актуальны.', $companyView->text);
        $this->assertEquals('Обновить страницу', $companyView->message);
        $this->assertEquals('centerNotificationCompany_bg-primary', $companyView->bgClass);
    }

    public function testLittleFilledCompanyProfileCompleteView(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithoutOwner::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadCompanyWithoutOwner::REFERENCE_NAME);
        assert($company instanceof Company);

        $companyViewFactory = $this->getContainer()->get(CompanyProfileCompletedViewFactory::class);
        $companyView = $companyViewFactory->create($company);

        $this->assertEquals(sprintf('Ваша страница заполнена всего на %s %%', $companyView->percentageOfCompleted), $companyView->header);
        $this->assertEquals('Расскажите подробнее о вашей компании, добавьте описание, фото, адреса, контакты и получите больше заинтересованных клиентов.', $companyView->text);
        $this->assertEquals('Заполнить страницу', $companyView->message);
        $this->assertEquals('centerNotificationCompany_bg-secondary', $companyView->bgClass);
    }
}
