<?php

namespace Tests\Unit\Domain\Record\Video\Validator\Constraint;

use App\Domain\Record\Video\Validator\Constraint\VideoIsAvailableValidator;
use App\Domain\Record\Video\Validator\Constraint\VideoIsEmbeddable;
use App\Domain\Record\Video\Validator\Constraint\VideoIsEmbeddableValidator;
use App\Module\VideoInformationLoader\VideoInformation;
use App\Module\VideoInformationLoader\VideoInformationLoaderMock;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

/**
 * @group video
 */
class VideoIsEmbeddableValidatorTest extends TestCase
{
    /** @var ValidatorExecutionContextMock */
    private $validatorExecutionContextMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validatorExecutionContextMock = new ValidatorExecutionContextMock();
    }

    protected function tearDown(): void
    {
        unset($this->validatorExecutionContextMock);

        parent::tearDown();
    }

    public function testValidationShouldFailIfVideoIsNotEmbeddable(): void
    {
        $validator = new VideoIsEmbeddableValidator(new VideoInformationLoaderMock($this->createVideoInformation()));
        $validator->initialize($this->validatorExecutionContextMock);

        $validator->validate('https://some_url', new VideoIsEmbeddable());

        $this->assertTrue($this->validatorExecutionContextMock->hasViolations());
    }

    public function testValidationShouldBePassedIfVideoIsEmbeddable(): void
    {
        $embeddableVideo = $this->createVideoInformation('some iframe');

        $validator = new VideoIsEmbeddableValidator(new VideoInformationLoaderMock($embeddableVideo));
        $validator->initialize($this->validatorExecutionContextMock);

        $validator->validate('https://some_url', new VideoIsEmbeddable());

        $this->assertFalse($this->validatorExecutionContextMock->hasViolations());
    }

    public function testValidationMustBeSkippedForNotAvailableVideo(): void
    {
        $validator = new VideoIsEmbeddableValidator(new VideoInformationLoaderMock());
        $validator->initialize($this->validatorExecutionContextMock);

        $validator->validate('https://some_url', new VideoIsEmbeddable());

        $this->assertFalse($this->validatorExecutionContextMock->hasViolations());
    }

    public function testValidationMustBeSkippedForEmptyVideoUrl(): void
    {
        $validator = new VideoIsEmbeddableValidator(new VideoInformationLoaderMock());
        $validator->initialize($this->validatorExecutionContextMock);

        $validator->validate('', new VideoIsEmbeddable());

        $this->assertFalse($this->validatorExecutionContextMock->hasViolations());
    }

    public function testValidationShouldFailForUnsupportedConstraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint must be instance');

        $validator = new VideoIsAvailableValidator(new VideoInformationLoaderMock());
        $validator->validate(null, $this->createMock(Constraint::class));
    }

    private function createVideoInformation(?string $htmlIframe = null): VideoInformation
    {
        $videoInformation = new VideoInformation(
            'url',
            'title',
            'imageUrl',
            $htmlIframe
        );

        return $videoInformation;
    }
}
