<?php

namespace Tests\Functional\Domain\Record\Tackle\Command;

use App\Domain\Record\Tackle\Command\TackleBrand\CreateTackleBrandCommand;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\Record\LoadTackleBrands;
use Tests\Functional\ValidationTestCase;

/**
 * @group tackle
 * @group tackle-brand
 */
class CreateTackleBrandCommandValidationTest extends ValidationTestCase
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
        $this->assertOnlyFieldsAreInvalid(new CreateTackleBrandCommand(), ['title'], null, 'Значение не должно быть пустым.');
    }

    public function testUniqueField(): void
    {
        $existsBrand = $this->referenceRepository->getReference(LoadTackleBrands::getRandReferenceName());
        $command = new CreateTackleBrandCommand();
        $command->title = $existsBrand->getTitle();

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('title', 'Такой бренд уже существует.');
    }
}
