<?php

namespace Tests\Unit\Module\Mailer\SwiftMailer\Normalizer;

use App\Module\Mailer\SwiftMailer\Normalizer\MessageFilesNormalizer;
use Swift_Mime_Attachment;
use Tests\Unit\TestCase;

/**
 * @group mailer
 */
class MessageFilesNormalizerTest extends TestCase
{
    /** @var MessageFilesNormalizer */
    private $messageFilesNormalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->messageFilesNormalizer = new MessageFilesNormalizer();
    }

    protected function tearDown(): void
    {
        unset(
            $this->messageFilesNormalizer
        );

        parent::tearDown();
    }

    public function testNormalizeWithEmptyArray()
    {
        $files = [];

        $expectedNormalizedData = [];

        $this->assertEquals($expectedNormalizedData, $this->messageFilesNormalizer->normalize($files));
    }

    public function testNormalize()
    {
        $files = [
            $this->createSwiftAttachmentMock(),
        ];

        $expectedNormalizedData = [
            'logo.png' => [
                'fileContent' => 'body',
                'filename' => 'logo.png',
            ],
        ];

        $this->assertEquals($expectedNormalizedData, $this->messageFilesNormalizer->normalize($files));
    }

    private function createSwiftAttachmentMock(): Swift_Mime_Attachment
    {
        $mock = $this->createMock(Swift_Mime_Attachment::class);

        $mock->method('getFilename')
            ->willReturn('logo.png');

        $mock->method('getBody')
            ->willReturn('body');

        return $mock;
    }
}
