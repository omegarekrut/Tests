<?php

namespace Tests\Functional\Domain\Record\Common\Command\UpdateRecord;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\Common\Command\UpdateRecord\UpdateRecordAuthorCommand;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\User\Entity\User;
use Prophecy\PhpUnit\ProphecyTrait;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadCompanyArticleWithAuthor;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\ValidationTestCase;

/**
 * @group record
 */
class UpdateRecordAuthorCommandValidationTest extends ValidationTestCase
{
    use ProphecyTrait;

    private $referenceRepository;
    private $user;
    private $companyArticle;
    private $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
            LoadCompanyArticleWithAuthor::class,
            LoadCompanyWithOwner::class,
        ])->getReferenceRepository();

        $this->user = $this->referenceRepository->getReference(LoadTestUser::USER_TEST);
        assert($this->user instanceof User);

        $this->companyArticle = $this->referenceRepository->getReference(LoadCompanyArticleWithAuthor::REFERENCE_NAME);
        assert($this->companyArticle instanceof CompanyArticle);

        $this->company = $this->referenceRepository->getReference(LoadCompanyWithOwner::REFERENCE_NAME);
        assert($this->company instanceof Company);
    }

    protected function tearDown(): void
    {
        unset(
            $this->referenceRepository,
            $this->user
        );

        parent::tearDown();
    }

    public function testToDoNothing(): void
    {
        $command = new UpdateRecordAuthorCommand($this->mockRecord());
        $command->author = $this->user->getUsername();

        $this->getValidator()->validate($command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }

    public function testUserExist(): void
    {
        $command = new UpdateRecordAuthorCommand($this->mockRecord());
        $command->author = 'doesNotExistUsername';

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('author', sprintf('Пользователь doesNotExistUsername на нашем сайте не найден.', $command->author));
    }

    public function testRecordIsCompanyArticleAndCompanyIsNotEmpty(): void
    {
        $command = new UpdateRecordAuthorCommand($this->companyArticle);
        $command->author = $this->user->getUsername();
        $command->company = $this->company;

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('record', 'Для данной записи запрещено выбирать публикацию от имени компании.');
    }

    public function testRecordIsCompanyArticleAndCompanyIsEmpty(): void
    {
        $command = new UpdateRecordAuthorCommand($this->companyArticle);
        $command->author = $this->user->getUsername();

        $this->getValidator()->validate($command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }

    private function mockRecord(): Record
    {
        $record = $this->prophesize(Record::class);

        return $record->reveal();
    }
}
