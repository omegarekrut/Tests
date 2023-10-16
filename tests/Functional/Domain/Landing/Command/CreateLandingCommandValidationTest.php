<?php

namespace Tests\Functional\Domain\Landing\Command;

use App\Domain\Landing\Command\CreateLandingCommand;
use Tests\DataFixtures\ORM\Landing\LoadTestLandings;
use Tests\Functional\ValidationTestCase;

/**
 * @group landing
 */
class CreateLandingCommandValidationTest extends ValidationTestCase
{
    /** @var CreateLandingCommand */
    private $createLandingCommand;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createLandingCommand = new CreateLandingCommand();
    }

    protected function tearDown(): void
    {
        unset($this->createLandingCommand);

        parent::tearDown();
    }

    public function testNotUniqueSlug(): void
    {
        $referenceRepository = $this->loadFixtures([LoadTestLandings::class])->getReferenceRepository();
        $landing = $referenceRepository->getReference(LoadTestLandings::REFERENCE_NAME);

        $this->createLandingCommand->slug = $landing->getSlug();

        $this->getValidator()->validate($this->createLandingCommand);

        $this->assertFieldInvalid(
            'slug',
            sprintf('Страница \'%s\' уже существует.', $this->createLandingCommand->slug)
        );
    }

    public function testInvalidSlugFormat(): void
    {
        $this->createLandingCommand->slug = $this->getFaker()->name;

        $this->getValidator()->validate($this->createLandingCommand);

        $this->assertFieldInvalid(
            'slug',
            'Разрешены только латинские буквы, цифры, \'-\' и \'_\'.'
        );
    }

    public function testNotBlankFields(): void
    {
        $this->assertOnlyFieldsAreInvalid(
            $this->createLandingCommand,
            ['hashtag', 'heading', 'slug'],
            null,
            'Значение не должно быть пустым.'
        );
    }

    public function testInvalidLength(): void
    {
        $this->assertOnlyFieldsAreInvalid(
            $this->createLandingCommand,
            ['heading', 'slug'],
            $this->getFaker()->realText(500),
            'Значение слишком длинное. Должно быть равно 128 символам или меньше.'
        );

        $this->assertOnlyFieldsAreInvalid(
            $this->createLandingCommand,
            ['metaTitle'],
            $this->getFaker()->realText(500),
            'Значение meta title слишком длинное. Должно быть равно 255 символам или меньше.');
    }
}
