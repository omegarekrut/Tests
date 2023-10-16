<?php

namespace Tests\Functional\Domain\Record\Tidings\Command\SemanticLink;

use App\Domain\Record\Tidings\Entity\Tidings;
use App\Domain\Record\Tidings\Command\SemanticLink\SyncTidingsSemanticLinksWithTextCommand;
use Tests\DataFixtures\ORM\Record\Tidings\LoadTidingsForSemanticLinks;
use Tests\Functional\ValidationTestCase;

/**
 * @group semantic_link
 */
class SyncTidingsSemanticLinksWithTextCommandValidationTest extends ValidationTestCase
{
    public function testTidingsFound(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTidingsForSemanticLinks::class,
        ])->getReferenceRepository();

        /** @var Tidings $tidings */
        $tidings = $referenceRepository->getReference(LoadTidingsForSemanticLinks::REFERENCE_NAME);

        $command = new SyncTidingsSemanticLinksWithTextCommand($tidings->getId());

        $this->getValidator()->validate($command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }

    public function testTidingsNotFound(): void
    {
        $command = new SyncTidingsSemanticLinksWithTextCommand(0005);

        $this->getValidator()->validate($command);

        $this->assertNotEmpty($this->getValidator()->getLastErrors());
        $this->assertFieldInvalid('tidingsId', 'Запись не найдена.');
    }
}
