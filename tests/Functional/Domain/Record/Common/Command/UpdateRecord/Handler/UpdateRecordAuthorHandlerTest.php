<?php

namespace Tests\Functional\Domain\Record\Common\Command\UpdateRecord\Handler;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\Common\Command\UpdateRecord\UpdateRecordAuthorCommand;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\User\Entity\User;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\User\LoadUserWithoutRecords;
use Tests\Functional\TestCase;

/**
 * @group record
 */
class UpdateRecordAuthorHandlerTest extends TestCase
{
    /** @var ReferenceRepository */
    private $referenceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadArticles::class,
            LoadUserWithoutRecords::class,
            LoadAquaMotorcycleShopsCompany::class,
        ])->getReferenceRepository();
    }

    protected function tearDown(): void
    {
        unset(
            $this->referenceRepository
        );

        parent::tearDown();
    }

    public function testUpdate(): void
    {
        $record = $this->referenceRepository->getReference(LoadArticles::getRandReferenceName());
        assert($record instanceof Record);

        $newRecordAuthor = $this->referenceRepository->getReference(LoadUserWithoutRecords::REFERENCE_NAME);
        assert($newRecordAuthor instanceof User);

        $newRecordCompany = $this->referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        assert($newRecordCompany instanceof Company);

        $updateRecordAuthorCommand = new UpdateRecordAuthorCommand($record);
        $updateRecordAuthorCommand->author = $newRecordAuthor->getLogin();
        $updateRecordAuthorCommand->company = $newRecordCompany;

        $this->getCommandBus()->handle($updateRecordAuthorCommand);

        $this->assertTrue($newRecordAuthor === $record->getAuthor());
        $this->assertTrue($newRecordCompany === $record->getCompanyAuthor());
    }
}
