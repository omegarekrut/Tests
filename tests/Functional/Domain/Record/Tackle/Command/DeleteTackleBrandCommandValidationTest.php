<?php

namespace Tests\Functional\Domain\Record\Tackle\Command;

use App\Domain\Record\Tackle\Command\TackleBrand\DeleteTackleBrandCommand;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\Record\LoadTackles;
use Tests\Functional\ValidationTestCase;

/**
 * @group tackle
 * @group tackle-brand
 */
class DeleteTackleBrandCommandValidationTest extends ValidationTestCase
{
    /** @var ReferenceRepository */
    private $referenceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadTackles::class,
        ])->getReferenceRepository();
    }

    protected function tearDown(): void
    {
        unset($this->referenceRepository);

        parent::tearDown();
    }

    public function testNotEmptyTackles(): void
    {
        $tackle = $this->referenceRepository->getReference(LoadTackles::getRandReferenceName());
        $command = new DeleteTackleBrandCommand($tackle->getBrand());

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('emptyTackles', 'Нельзя удалить бренд, для которого существует снасть.');
    }
}
