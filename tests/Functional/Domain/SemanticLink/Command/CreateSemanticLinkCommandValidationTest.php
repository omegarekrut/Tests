<?php

namespace Tests\Functional\Domain\SemanticLink\Command;

use App\Domain\SemanticLink\Command\CreateSemanticLinkCommand;
use App\Domain\SemanticLink\Entity\SemanticLink;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\SemanticLink\LoadSemanticLinkWithInvalidUri;
use Tests\DataFixtures\ORM\SemanticLink\LoadSemanticLinkWithValidUri;
use Tests\Functional\ValidationTestCase;

/**
 * @group semantic_link
 */
class CreateSemanticLinkCommandValidationTest extends ValidationTestCase
{
    /** @var CreateSemanticLinkCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new CreateSemanticLinkCommand(Uuid::uuid4());
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testValidateWithEmptyErrors(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadSemanticLinkWithValidUri::class,
        ])->getReferenceRepository();

        /** @var SemanticLink $semanticLinkWithValidUri */
        $semanticLinkWithValidUri = $referenceRepository->getReference(LoadSemanticLinkWithValidUri::REFERENCE_NAME);

        $this->command->text = $semanticLinkWithValidUri->getText().' unique';
        $this->command->uri = $semanticLinkWithValidUri->getUri();

        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }

    public function testNotUniqueText(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadSemanticLinkWithValidUri::class,
        ])->getReferenceRepository();

        /** @var SemanticLink $expectedSemanticLink */
        $expectedSemanticLink = $referenceRepository->getReference(LoadSemanticLinkWithValidUri::REFERENCE_NAME);

        $this->command->text = $expectedSemanticLink->getText();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('text', 'Ссылка с текстом \''.$this->command->text.'\' уже существует.');
    }

    public function testNotBlankFields(): void
    {
        $this->assertOnlyFieldsAreInvalid(
            $this->command,
            ['id', 'uri', 'text'],
            null,
            'Значение не должно быть пустым.'
        );
    }

    public function testInvalidLengthFields(): void
    {
        $this->assertOnlyFieldsAreInvalid(
            $this->command,
            ['uri', 'text'],
            $this->getFaker()->realText(300),
            'Длина не должна превышать 255 символов.'
        );
    }

    public function testInvalidUri(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadSemanticLinkWithInvalidUri::class,
        ])->getReferenceRepository();

        /** @var SemanticLink $semanticLinkWithInvalidUri */
        $semanticLinkWithInvalidUri = $referenceRepository->getReference(LoadSemanticLinkWithInvalidUri::REFERENCE_NAME);

        $this->command->uri = $semanticLinkWithInvalidUri->getUri();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('uri', 'Uri не является относительной ссылкой от корневого каталога');
    }
}
