<?php

namespace Tests\Functional\Module\FileDownloader;

use App\Module\FileDownloader\Exception\FailedToDownloadFileException;
use App\Module\FileDownloader\LocalStorageFileDownloader;
use Tests\Functional\TestCase;

class LocalStorageFileDownloaderTest extends TestCase
{
    private const FILE_URL = __FILE__; // Current file name uses to speedup test

    /** @var LocalStorageFileDownloader */
    private $fileDownloader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileDownloader = $this->getContainer()->get(LocalStorageFileDownloader::class);
    }

    protected function tearDown(): void
    {
        unset($this->fileDownloader);

        parent::tearDown();
    }

    public function testLoaderCanDownloadFileByUrl(): void
    {
        $temporaryImageFilename = $this->createTemporaryFileName();

        $this->fileDownloader->download(self::FILE_URL, $temporaryImageFilename);

        $this->assertGreaterThan(0, filesize($temporaryImageFilename));
    }

    public function testLoadMustFailForInvalidUrl(): void
    {
        $this->expectException(FailedToDownloadFileException::class);
        $this->expectExceptionMessage('Failed to read file content');

        $this->fileDownloader->download('invalid file url', $this->createTemporaryFileName());
    }

    public function testLoadMustFailForInvalidFile(): void
    {
        $this->expectException(FailedToDownloadFileException::class);
        $this->expectExceptionMessage('Failed to write content');

        $this->fileDownloader->download(self::FILE_URL, 'http://invalid.file.name');
    }

    private function createTemporaryFileName(): string
    {
        return tempnam(sys_get_temp_dir(), 'file_');
    }
}
