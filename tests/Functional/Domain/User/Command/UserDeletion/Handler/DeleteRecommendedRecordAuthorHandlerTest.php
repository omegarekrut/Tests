<?php

namespace Tests\Functional\Domain\User\Command\UserDeletion\Handler;

use App\Domain\Ban\Service\BanInterface;
use App\Domain\RecommendedRecord\Entity\RecommendedRecord;
use App\Domain\User\Command\Deleting\DeleteSpammerCommand;
use Tests\DataFixtures\ORM\RecommendedRecord\LoadRecommendedRecordForDeletingAuthor;
use Tests\Functional\TestCase;

class DeleteRecommendedRecordAuthorHandlerTest extends TestCase
{
    public function testAfterHandlingRecommendedRecordAuthorMustBeDeleted(): void
    {
        $banStorage = $this->getContainer()->get(BanInterface::class);

        $referenceRepository = $this->loadFixtures([
            LoadRecommendedRecordForDeletingAuthor::class,
        ])->getReferenceRepository();

        $recommendedRecord = $referenceRepository->getReference(LoadRecommendedRecordForDeletingAuthor::REFERENCE_NAME);
        assert($recommendedRecord instanceof RecommendedRecord);

        $recommendedRecordAuthor = $recommendedRecord->getRecord()->getAuthor();

        $command = new DeleteSpammerCommand();
        $command->spammerId = $recommendedRecordAuthor->getId();
        $command->cause = 'test removal';

        $this->getCommandBus()->handle($command);

        $banInformation = $banStorage->getBanInformationByUserId($recommendedRecordAuthor->getId());

        $this->assertNotNull($banInformation);
        $this->assertEquals($command->cause, $banInformation->getCause());

        $this->assertTrue($banStorage->isBannedByIp($recommendedRecordAuthor->getLastVisit()->getLastVisitIp()));
    }
}
