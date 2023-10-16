<?php

namespace Tests\Functional\Domain\Record\Tackle\Command;

use App\Domain\Record\Tackle\Command\TackleBrand\UpdateTackleBrandCommand;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\Record\LoadTackleBrands;
use Tests\Functional\ValidationTestCase;

/**
 * @group tackle
 * @group tackle-brand
 */
class UpdateTackleBrandCommandValidationTest extends ValidationTestCase
{
    /** @var ReferenceRepository */
    private $referenceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadTackleBrands::class,
        ])->getReferenceRepository();
    }

    protected function tearDown(): void
    {
        unset($this->referenceRepository);

        parent::tearDown();
    }

    public function testNotBlankField(): void
    {
        $command = new UpdateTackleBrandCommand($this->referenceRepository->getReference(LoadTackleBrands::getRandReferenceName()));
        $this->assertOnlyFieldsAreInvalid($command, ['title'], null, 'Значение не должно быть пустым.');
    }

    public function testUniqueField(): void
    {
        $existsBrand = $this->referenceRepository->getReference(LoadTackleBrands::getRandReferenceName());
        do {
            $existsBrand2 = $this->referenceRepository->getReference(LoadTackleBrands::getRandReferenceName());
        } while ($existsBrand->getTitle() === $existsBrand2->getTitle());

        $command = new UpdateTackleBrandCommand($existsBrand);
        $command->title = $existsBrand2->getTitle();

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('title', 'Такой бренд уже существует.');
    }
}
