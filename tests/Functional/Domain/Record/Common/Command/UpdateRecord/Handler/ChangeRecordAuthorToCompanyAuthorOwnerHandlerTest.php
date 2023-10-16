<?php

namespace Tests\Functional\Domain\Record\Common\Command\UpdateRecord\Handler;

use App\Domain\Record\Common\Command\UpdateRecord\ChangeRecordAuthorToCompanyAuthorOwnerCommand;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadCompanyArticleWithAuthor;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadCompanyArticleWithAuthorWithoutCompanyOwner;
use Tests\Functional\TestCase;

/**
 * @group record
 */
class ChangeRecordAuthorToCompanyAuthorOwnerHandlerTest extends TestCase
{
    private ReferenceRepository $referenceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadCompanyArticleWithAuthor::class,
            LoadCompanyArticleWithAuthorWithoutCompanyOwner::class,
        ])->getReferenceRepository();
    }

    public function testUpdateWithCompanyOwner(): void
    {
        $record = $this->referenceRepository->getReference(LoadCompanyArticleWithAuthor::REFERENCE_NAME);
        assert($record instanceof CompanyArticle);
        $changeRecordAuthorToCompanyAuthorOwnerCommand = new ChangeRecordAuthorToCompanyAuthorOwnerCommand($record);

        $this->getCommandBus()->handle($changeRecordAuthorToCompanyAuthorOwnerCommand);

        $this->assertEquals($record->getAuthor()->getId(), $record->getCompanyAuthor()->getOwner()->getId());
        $this->assertEquals($record->getAuthor()->getUsername(), $record->getAuthor()->getUsername());
        $this->assertNotEquals('DELETED', $record->getAuthor()->getUsername());
    }

    public function testUpdateWithoutCompanyOwner(): void
    {
        $record = $this->referenceRepository->getReference(LoadCompanyArticleWithAuthorWithoutCompanyOwner::REFERENCE_NAME);
        assert($record instanceof CompanyArticle);
        $changeRecordAuthorToCompanyAuthorOwnerCommand = new ChangeRecordAuthorToCompanyAuthorOwnerCommand($record);

        $this->getCommandBus()->handle($changeRecordAuthorToCompanyAuthorOwnerCommand);

        $this->assertEquals($record->getAuthor()->getId(), $record->getCompanyAuthor()->getOwner()->getId());
        $this->assertEquals('DELETED', $record->getAuthor()->getUsername());
    }
}
