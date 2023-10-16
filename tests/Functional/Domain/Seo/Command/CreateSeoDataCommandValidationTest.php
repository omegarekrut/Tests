<?php

namespace Tests\Functional\Domain\Seo\Command;

use App\Domain\Seo\Command\CreateSeoDataCommand;
use App\Domain\Seo\Entity\SeoData;
use Tests\DataFixtures\ORM\Seo\LoadSeoData;
use Tests\Functional\ValidationTestCase;

/**
 * @group seo
 */
class CreateSeoDataCommandValidationTest extends ValidationTestCase
{
    /** @var CreateSeoDataCommand */
    private $command;

    /** @var SeoData */
    private $expectedSeoData;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadSeoData::class,
        ])->getReferenceRepository();

        $this->expectedSeoData = $referenceRepository->getReference(LoadSeoData::WITH_QUERY_STRING_AND_OTHER_VALUE);
        $this->command = new CreateSeoDataCommand();
    }

    protected function tearDown(): void
    {
        unset(
            $this->expectedSeoData,
            $this->command
        );

        parent::tearDown();
    }

    public function testNotUniqueUri() : void
    {
        $this->command->uri = $this->expectedSeoData->getUri();
        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('uri', 'Uri \''.$this->command->uri.'\' уже существует.');
    }

    public function testInvalidLength(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['description'], $this->getFaker()->realText(500), 'Длина не должна превышать 300 символов.');

        $this->assertOnlyFieldsAreInvalid($this->command, ['uri', 'title', 'h1'], $this->getFaker()->realText(300), 'Длина не должна превышать 255 символов.');
    }
}
