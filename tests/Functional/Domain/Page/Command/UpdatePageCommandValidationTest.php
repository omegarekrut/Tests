<?php

namespace Tests\Functional\Domain\Page\Command;

use App\Domain\Page\Command\UpdatePageCommand;
use Tests\DataFixtures\ORM\LoadPages;
use Tests\Functional\ValidationTestCase;

/**
 * @group page
 */
class UpdatePageCommandValidationTest extends ValidationTestCase
{
    /**
     * @var UpdatePageCommand
     */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadPages::class,
        ])->getReferenceRepository();

        $this->command = new UpdatePageCommand($referenceRepository->getReference(LoadPages::getReferenceName(LoadPages::PAGE_ABOUT)));
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testToDoNothing(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
