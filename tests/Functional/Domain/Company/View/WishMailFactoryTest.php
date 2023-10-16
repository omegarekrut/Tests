<?php

namespace Tests\Functional\Domain\Company\View;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\View\WishMailFactory;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithoutOwner;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

class WishMailFactoryTest extends TestCase
{
    private User $user;
    private Company $company;
    private WishMailFactory $wishMailFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
            LoadCompanyWithoutOwner::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        $this->company = $referenceRepository->getReference(LoadCompanyWithoutOwner::REFERENCE_NAME);
        $this->wishMailFactory = $this->getContainer()->get(WishMailFactory::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->user,
            $this->wishMailFactory,
            $this->company,
        );

        parent::tearDown();
    }

    public function testBuildMail(): void
    {
        $expectedWishText = 'wish text';
        $expectedUrl = $this->wishMailFactory->companyViewUrlGenerator->generate($this->company);

        $swiftMessage = $this->wishMailFactory->buildMail($this->user, $expectedWishText, $this->company);

        $this->assertEquals('Сайт FishingSib.ru: пожелания компаний', $swiftMessage->getSubject());
        $this->assertEquals([(string) $this->user->getEmail() => null], $swiftMessage->getFrom());
        $this->assertStringContainsString($expectedWishText, $swiftMessage->getBody());
        $this->assertStringContainsString($expectedUrl, $swiftMessage->getBody());
        $this->assertStringContainsString($this->company->getName(), $swiftMessage->getBody());
    }
}
