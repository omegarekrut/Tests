<?php

namespace Tests\Functional\Domain\Seo\Command;

use App\Domain\Seo\Command\UpdateSeoDataCommand;
use Tests\DataFixtures\ORM\Seo\LoadSeoData;
use Tests\Functional\ValidationTestCase;

/**
 * @group seo
 */
class UpdateSeoDataCommandValidationTest extends ValidationTestCase
{
    public function testToDoNothing() : void
    {
        $referenceRepository = $this->loadFixtures([
            LoadSeoData::class,
        ])->getReferenceRepository();

        $command = new UpdateSeoDataCommand($referenceRepository->getReference(LoadSeoData::WITH_QUERY_STRING_AND_OTHER_VALUE));

        $this->getValidator()->validate($command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
