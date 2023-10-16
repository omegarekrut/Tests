<?php

namespace Tests\Functional\Domain\Record\CompanyReview\Command;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\CompanyReview\Command\CreateCompanyReviewCommand;
use App\Domain\User\Entity\User;
use App\Module\Author\AuthorInterface;
use App\Util\ImageStorage\Collection\ImageWithRotationAngleCollection;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\ValidationTestCase;

class CreateCompanyReviewCommandValidationTest extends ValidationTestCase
{
    private CreateCompanyReviewCommand $command;
    private User $authorIsOwnerCompany;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
            LoadAquaMotorcycleShopsCompany::class,
            LoadUserWithAvatar::class,
        ])->getReferenceRepository();

        $author = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        assert($author instanceof AuthorInterface);

        $this->authorIsOwnerCompany = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        assert($this->authorIsOwnerCompany instanceof AuthorInterface);

        $this->company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        assert($this->company instanceof Company);

        $this->company->setOwner($this->authorIsOwnerCompany);

        $this->command = new CreateCompanyReviewCommand($author, $this->company);
        $this->command->text = $this->getFaker()->realText(200);
        $this->command->images = $this->createMock(ImageWithRotationAngleCollection::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->command,
            $this->authorIsOwnerCompany,
            $this->company
        );

        parent::tearDown();
    }

    public function testNotBlankTextReview(): void
    {
        $this->command->text = '';

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('text', 'Это поле обязательно для заполнения.');
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

    public function testOwnerOfCompanyCannotWriteReview(): void
    {
        $this->command = new CreateCompanyReviewCommand($this->authorIsOwnerCompany, $this->company);

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid(
            'author',
            sprintf(
                'Запрещено оставлять отзывы на свою компанию. Автор %s является владельцем компании %s.',
                $this->authorIsOwnerCompany->getUsername(),
                $this->company->getName(),
            ),
        );
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
