<?php

namespace Tests\Unit\Domain\Record\Video\Validator\Constraint;

use App\Domain\Record\Video\Entity\Video;
use App\Domain\Record\Video\Repository\VideoRepository;
use App\Domain\Record\Video\Validator\Constraint\VideoIsNotDuplicate;
use App\Domain\Record\Video\Validator\Constraint\VideoIsNotDuplicateValidator;
use App\Module\VideoInformationLoader\VideoInformation;
use App\Module\VideoInformationLoader\VideoInformationLoaderInterface;
use App\Module\VideoInformationLoader\VideoInformationLoaderMock;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

/**
 * @group video
 */
class VideoIsNotDuplicatedValidatorTest extends TestCase
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
        unset(
            $this->validatorExecutionContextMock
        );

        parent::tearDown();
    }

    public function testValidationShouldFailIfVideoExists(): void
    {
        /** @var Video $existingVideo */
        $existingVideo = $this->createConfiguredMock(Video::class, [
            'getVideoUrl' => 'https://www.youtube.com/watch?v=nL5d37iqWIU&t=1s',
        ]);

        $videoRepository = $this->createVideoRepositoryForFindByIframeUrl($existingVideo);
        $videoInformationLoader = $this->createVideoInformationLoaderInterfaceMock('<iframe src="'.$existingVideo->getVideoUrl().'" ></iframe>');

        $validator = new VideoIsNotDuplicateValidator($videoRepository, $videoInformationLoader);
        $validator->initialize($this->validatorExecutionContextMock);

        $validator->validate($existingVideo->getVideoUrl(), new VideoIsNotDuplicate());

        $this->assertTrue($this->validatorExecutionContextMock->hasViolations());
    }

    public function testValidationShouldBePassedForNewVideo(): void
    {
        $videoRepository = $this->createVideoRepositoryForFindByIframeUrl();

        $validator = new VideoIsNotDuplicateValidator($videoRepository, $this->createVideoInformationLoaderInterfaceMock());
        $validator->initialize($this->validatorExecutionContextMock);

        $validator->validate('https://www.youtube.com/watch?v=nL5d37iqWIU&t=1s', new VideoIsNotDuplicate());

        $this->assertFalse($this->validatorExecutionContextMock->hasViolations());
    }

    public function testValidationMustBeSkippedForEmptyVideoUrl(): void
    {
        $videoRepository = $this->createVideoRepositoryForFindByIframeUrl();
        $videoInformationLoader = $this->createVideoInformationLoaderInterfaceMock();

        $validator = new VideoIsNotDuplicateValidator($videoRepository, $videoInformationLoader);
        $validator->initialize($this->validatorExecutionContextMock);

        $validator->validate('', new VideoIsNotDuplicate());

        $this->assertFalse($this->validatorExecutionContextMock->hasViolations());
    }

    public function testValidationMustBeSkippedForNotAvailableVideo(): void
    {
        $videoRepository = $this->createVideoRepositoryForFindByIframeUrl();
        $videoInformationLoader = new VideoInformationLoaderMock();

        $validator = new VideoIsNotDuplicateValidator($videoRepository, $videoInformationLoader);
        $validator->initialize($this->validatorExecutionContextMock);

        $validator->validate('https://some_url', new VideoIsNotDuplicate());

        $this->assertFalse($this->validatorExecutionContextMock->hasViolations());
    }

    public function testValidationMustBeSkippedForNotEmbeddableVideo(): void
    {
        $videoRepository = $this->createVideoRepositoryForFindByIframeUrl();
        $videoInformationLoader = $this->createVideoInformationLoaderInterfaceMock();

        $validator = new VideoIsNotDuplicateValidator($videoRepository, $videoInformationLoader);
        $validator->initialize($this->validatorExecutionContextMock);

        $validator->validate('https://some_url', new VideoIsNotDuplicate());

        $this->assertFalse($this->validatorExecutionContextMock->hasViolations());
    }

    private function createVideoInformationLoaderInterfaceMock(?string $htmlIframe = null): VideoInformationLoaderInterface
    {
        return new VideoInformationLoaderMock($this->createVideoInformation($htmlIframe));
    }

    private function createVideoInformation(?string $htmlIframe = null): VideoInformation
    {
        $videoInformation = new VideoInformation(
            'url',
            'title',
            'imageUrl',
            $htmlIframe ?: '<iframe src="https://www.youtube.com/embed/2tQ4hLQWMMk" ></iframe>'
        );

        return $videoInformation;
    }

    private function createVideoRepositoryForFindByIframeUrl(?Video $video = null): VideoRepository
    {
        $stub = $this->createMock(VideoRepository::class);
        $stub
            ->method('findVideoByIframeUrl')
            ->willReturn($video);

        return $stub;
    }
}
