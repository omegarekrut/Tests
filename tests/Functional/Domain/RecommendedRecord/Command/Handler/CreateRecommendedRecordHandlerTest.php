<?php

namespace Tests\Functional\Domain\RecommendedRecord\Command\Handler;

use App\Domain\RecommendedRecord\Command\CreateRecommendedRecordCommand;
use App\Domain\RecommendedRecord\Repository\RecommendedRecordRepository;
use App\Domain\Record\Common\Entity\Record;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadCompanyArticleWithRecordSemanticLink;
use Tests\Functional\TestCase;

class CreateRecommendedRecordHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyArticleWithRecordSemanticLink::class,
        ])->getReferenceRepository();

        $recommendedRecordRepository = $this->getContainer()->get(RecommendedRecordRepository::class);
        $record = $referenceRepository->getReference(LoadCompanyArticleWithRecordSemanticLink::REFERENCE_NAME);
        assert($record instanceof Record);

        $command = new CreateRecommendedRecordCommand(Uuid::uuid4(), $record);
        $this->getCommandBus()->handle($command);

        $recommendedRecord = $recommendedRecordRepository->findByRecordId($record->getId());

        $this->assertEquals($record->getTitle(), $recommendedRecord->getRecord()->getTitle());
    }
}
