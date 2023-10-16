<?php

namespace Tests\Unit\Module\ShortUuid;

use App\Module\ShortUuid\ShortUuidConverter;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ShortUuidConverterTest extends TestCase
{
    private const UUID = '4e52c919-513e-4562-9248-7dd612c6c1ca';
    private const SHORT_UUID = 'fpfyRTmt6XeE9ehEKZ5LwF';

    public function testEncode(): void
    {
        $shortUuidConverter = new ShortUuidConverter();
        $shortUuid = $shortUuidConverter->encode(Uuid::fromString(self::UUID));

        $this->assertEquals(self::SHORT_UUID, $shortUuid);
    }

    public function testDecode(): void
    {
        $shortUuidConverter = new ShortUuidConverter();
        $uuid = $shortUuidConverter->decode(self::SHORT_UUID);

        $this->assertEquals(self::UUID, (string) $uuid);
    }
}
