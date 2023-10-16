<?php

namespace Tests\Unit\Domain\Record\Video\Validator\Constraint;

use App\Domain\Record\Video\Validator\Constraint\VideoIsAvailable;
use App\Domain\Record\Video\Validator\Constraint\VideoIsAvailableValidator;
use App\Module\VideoInformationLoader\VideoInformation;
use App\Module\VideoInformationLoader\VideoInformationLoaderMock;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

/**
 * @group video
 */
class VideoIsAvailableValidatorTest extends TestCase
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

    public function testValidationShouldFailIfVideoIsNotAvailable(): void
    {
        $validator = new VideoIsAvailableValidator(new VideoInformationLoaderMock());
        $validator->initialize($this->validatorExecutionContextMock);

        $validator->validate('https://www.youtube.com/watch?v=nL5d37iqWIU&t=1s', new VideoIsAvailable());

        $this->assertTrue($this->validatorExecutionContextMock->hasViolations());
    }

    public function testValidationShouldBePassedIfVideoIsAvailable(): void
    {
        $validator = new VideoIsAvailableValidator(new VideoInformationLoaderMock($this->createVideoInformation()));
        $validator->initialize($this->validatorExecutionContextMock);

        $validator->validate('https://www.youtube.com/watch?v=nL5d37iqWIU&t=1s', new VideoIsAvailable());

        $this->assertFalse($this->validatorExecutionContextMock->hasViolations());
    }

    public function testValidationMustBeSkippedForEmptyVideoUrl(): void
    {
        $validator = new VideoIsAvailableValidator(new VideoInformationLoaderMock());
        $validator->initialize($this->validatorExecutionContextMock);

        $validator->validate('', new VideoIsAvailable());

        $this->assertFalse($this->validatorExecutionContextMock->hasViolations());
    }

    public function testValidationShouldFailForUnsupportedConstraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint must be instance');

        $validator = new VideoIsAvailableValidator(new VideoInformationLoaderMock());
        $validator->validate(null, $this->createMock(Constraint::class));
    }

    private function createVideoInformation(): VideoInformation
    {
        $videoInformation = new VideoInformation('url');

        return $videoInformation;
    }
}
