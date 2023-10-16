<?php

namespace Tests\Unit\Util\ImageStorage;

use App\Util\ImageStorage\Exception\FailedToDeleteImageException;
use App\Util\ImageStorage\Exception\FailedToGetImageInformationException;
use App\Util\ImageStorage\Exception\FailedToTransformImageException;
use App\Util\ImageStorage\Exception\FailedToUploadImageException;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ImageInformation;
use App\Util\ImageStorage\ImageStorageServerClient;
use App\Util\ImageStorage\ImageTransformer;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Tests\Unit\TestCase;

class ImageStorageServerClientTest extends TestCase
{
    /** @var \SplFileInfo */
    private $imageFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->imageFile = new \SplFileInfo(__FILE__);
    }

    protected function tearDown(): void
    {
        unset($this->imageFile);

        parent::tearDown();
    }

    public function testImageFileCanBeUploadedToServer(): void
    {
        $expectedImageName = 'image-id';
        $expectedImageExtension = 'jpg';
        $expectedWidth = 1920;
        $expectedHeight = 1080;

        $successfulClientResponse = new Response(201, [], json_encode([
            'response' => [
                'file_id' => $expectedImageName,
                'file_ext' => $expectedImageExtension,
                'file_width' => $expectedWidth,
                'file_height' => $expectedHeight,
            ],
        ]));

        $client = $this->createClientForReturnResponse($successfulClientResponse);
        $imageStorage = $this->createImageStorageServiceClient($client);

        $imageInformation = $imageStorage->upload($this->imageFile);

        $this->assertEquals($expectedImageName, $imageInformation->getImage()->getName());
        $this->assertEquals($expectedImageExtension, $imageInformation->getImage()->getExtension());
        $this->assertEquals($expectedWidth, $imageInformation->getSize()->getWidth());
        $this->assertEquals($expectedHeight, $imageInformation->getSize()->getHeight());
    }

    public function testNotExistingImageCannotBeUploadedToServer(): void
    {
        $this->expectException(FailedToUploadImageException::class);
        $this->expectExceptionMessage('Image file isn\'t readable.');

        $client = $this->createMock(ClientInterface::class);
        $imageStorage = $this->createImageStorageServiceClient($client);

        $notExistingImageFile = new \SplFileInfo('not-existsing.file');

        $imageStorage->upload($notExistingImageFile);
    }

    public function testUploadingProcessMustFailsForInvalidServerResponseStatus(): void
    {
        $this->expectException(FailedToUploadImageException::class);
        $this->expectExceptionMessage('403 Forbidden');

        $failedClientResponse = new Response(403, [], json_encode([
            'code' => 403,
        ]));

        $client = $this->createClientForReturnResponse($failedClientResponse);
        $imageStorage = $this->createImageStorageServiceClient($client);

        $imageStorage->upload($this->imageFile);
    }

    public function testUploadingProcessMustFailsForInvalidResponseData(): void
    {
        $this->expectException(FailedToUploadImageException::class);
        $this->expectExceptionMessage('Failed to upload image file to server. Response data doesn\'t contain file id or extension.');

        $failedClientResponse = new Response(200, [], json_encode([
            'invalid key' => 'invalid data',
        ]));

        $client = $this->createClientForReturnResponse($failedClientResponse);
        $imageStorage = $this->createImageStorageServiceClient($client);

        $imageStorage->upload($this->imageFile);
    }

    public function testDeletionMustNotCauseException(): void
    {
        $image = new Image('image.file');

        $successfulClientResponse = new Response(201, [], json_encode([
            'response' => [
                (string) $image => [
                    ['command' => 'delete', 'status' => 'success'],
                ],
            ],
        ]));

        $client = $this->createClientForReturnResponse($successfulClientResponse);
        $imageStorage = $this->createImageStorageServiceClient($client);

        $actualException = null;

        try {
            $imageStorage->delete($image);
        } catch (\Throwable $exception) {
            $actualException = $exception;
        }

        $this->assertEmpty($actualException);
    }

    public function testDeletionMustFailsForInvalidServerResponseStatus(): void
    {
        $this->expectException(FailedToDeleteImageException::class);
        $this->expectExceptionMessage('403 Forbidden');

        $image = new Image('image.file');

        $failedClientResponse = new Response(403, [], json_encode([
            'code' => 403,
        ]));

        $client = $this->createClientForReturnResponse($failedClientResponse);
        $imageStorage = $this->createImageStorageServiceClient($client);

        $imageStorage->delete($image);
    }

    public function testDeletionMustFailsForInvalidResponseData(): void
    {
        $this->expectException(FailedToDeleteImageException::class);
        $this->expectExceptionMessage('Command "delete" didn\'t successfully execute.');

        $image = new Image('image.file');

        $failedClientResponse = new Response(200, [], json_encode([]));

        $client = $this->createClientForReturnResponse($failedClientResponse);
        $imageStorage = $this->createImageStorageServiceClient($client);

        $imageStorage->delete($image);
    }

    public function testImageCanBeTransformed(): void
    {
        $image = new Image('image.file');
        $imageTransformer = $this->createImageTransformer($image);

        $expectedNewImageName = 'new_image_name';

        $successfulClientResponse = new Response(201, [], json_encode([
            'response' => [
                (string) $image => [
                    ['command' => 'modify', 'status' => 'success', 'file_id_new' => $expectedNewImageName],
                ],
            ],
        ]));

        $client = $this->createClientForReturnResponse($successfulClientResponse);
        $imageStorage = $this->createImageStorageServiceClient($client);

        $transformedImage = $imageStorage->transform($imageTransformer);

        $this->assertEquals($expectedNewImageName, $transformedImage->getName());
        $this->assertEquals($image->getExtension(), $transformedImage->getExtension());
    }

    public function testTransformationMustFailsForInvalidServerResponseStatus(): void
    {
        $this->expectException(FailedToTransformImageException::class);
        $this->expectExceptionMessage('403 Forbidden');

        $imageTransformer = $this->createImageTransformer(new Image('image.file'));

        $failedClientResponse = new Response(403, [], json_encode([
            'code' => 403,
        ]));

        $client = $this->createClientForReturnResponse($failedClientResponse);
        $imageStorage = $this->createImageStorageServiceClient($client);

        $imageStorage->transform($imageTransformer);
    }

    public function testTransformationMustFailsForInvalidResponseData(): void
    {
        $this->expectException(FailedToTransformImageException::class);
        $this->expectExceptionMessage('Command "modify" didn\'t successfully execute.');

        $imageTransformer = $this->createImageTransformer(new Image('image.file'));

        $failedClientResponse = new Response(200, [], json_encode([]));

        $client = $this->createClientForReturnResponse($failedClientResponse);
        $imageStorage = $this->createImageStorageServiceClient($client);

        $imageStorage->transform($imageTransformer);
    }

    public function testGetImageInformationMustFailsForEmptyResponseData(): void
    {
        $this->expectException(FailedToGetImageInformationException::class);
        $this->expectExceptionMessage('Failed get image file information from server.');

        $failedClientResponse = new Response(200, [], json_encode([]));

        $client = $this->createClientForReturnResponse($failedClientResponse);
        $imageStorage = $this->createImageStorageServiceClient($client);

        $imageStorage->getImageInformation(new Image('image.file'));
    }

    public function testGetImageInformationMustFailsForInvalidWidthAndHeightResponseData(): void
    {
        $this->expectException(FailedToGetImageInformationException::class);
        $this->expectExceptionMessage('Failed get image file information from server.');

        $failedClientResponse = new Response(200, [], json_encode([
            'response' => [
                [
                    'image' => 'image.file',
                    'width' => null,
                    'height' => null,
                ],
            ],
        ]));

        $client = $this->createClientForReturnResponse($failedClientResponse);
        $imageStorage = $this->createImageStorageServiceClient($client);

        $imageStorage->getImageInformation(new Image('image.file'));
    }

    public function testGetImageInformationMustFailsForInvalidImageNameResponseData(): void
    {
        $this->expectException(FailedToGetImageInformationException::class);
        $this->expectExceptionMessage('Failed get image file information from server.');

        $failedClientResponse = new Response(200, [], json_encode([
            'response' => [
                [
                    'image' => 'another.file',
                    'width' => 1600,
                    'height' => 1600,
                ],
            ],
        ]));

        $client = $this->createClientForReturnResponse($failedClientResponse);
        $imageStorage = $this->createImageStorageServiceClient($client);

        $imageStorage->getImageInformation(new Image('image.file'));
    }

    public function testGetImageInformationMustFailsForInvalidWidthResponseData(): void
    {
        $this->expectException(FailedToGetImageInformationException::class);
        $this->expectExceptionMessage('Failed get image file information from server.');

        $failedClientResponse = new Response(200, [], json_encode([
            'response' => [
                [
                    'image' => 'image.file',
                    'width' => null,
                    'height' => 1600,
                ],
            ],
        ]));

        $client = $this->createClientForReturnResponse($failedClientResponse);
        $imageStorage = $this->createImageStorageServiceClient($client);

        $imageStorage->getImageInformation(new Image('image.file'));
    }

    public function testGetImageInformationMustFailsForInvalidHeightResponseData(): void
    {
        $this->expectException(FailedToGetImageInformationException::class);
        $this->expectExceptionMessage('Failed get image file information from server.');

        $failedClientResponse = new Response(200, [], json_encode([
            'response' => [
                [
                    'image' => 'image.file',
                    'width' => 1600,
                    'height' => null,
                ],
            ],
        ]));

        $client = $this->createClientForReturnResponse($failedClientResponse);
        $imageStorage = $this->createImageStorageServiceClient($client);

        $imageStorage->getImageInformation(new Image('image.file'));
    }

    public function testGetImageInformationMustReturnImageInformation(): void
    {
        $clientResponse = new Response(200, [], json_encode([
            'response' => [
                [
                    'image' => 'image.file',
                    'width' => 1600,
                    'height' => 1200,
                ],
            ],
        ]));

        $client = $this->createClientForReturnResponse($clientResponse);
        $imageStorage = $this->createImageStorageServiceClient($client);

        $image = new Image('image.file');

        $imageInformation = $imageStorage->getImageInformation($image);

        $this->assertInstanceOf(ImageInformation::class, $imageInformation);
        $this->assertEquals((string) $image, (string) $imageInformation->getImage());
        $this->assertEquals(1600, $imageInformation->getSize()->getWidth());
        $this->assertEquals(1200, $imageInformation->getSize()->getHeight());
    }

    private function createClientForReturnResponse(ResponseInterface $response): ClientInterface
    {
        $mock = new MockHandler([$response]);
        $handlerStack = HandlerStack::create($mock);

        return new Client(['handler' => $handlerStack]);
    }

    private function createImageStorageServiceClient(ClientInterface $client): ImageStorageServerClient
    {
        return new ImageStorageServerClient($client, 'storage url', 'client id', 'client key');
    }

    private function createImageTransformer(Image $image): ImageTransformer
    {
        $stub = $this->createMock(ImageTransformer::class);
        $stub
            ->method('getImage')
            ->willReturn($image);
        $stub
            ->method('getExtension')
            ->willReturn($image->getExtension());

        return $stub;
    }
}
