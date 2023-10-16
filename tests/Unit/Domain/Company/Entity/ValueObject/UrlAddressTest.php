<?php

namespace Tests\Unit\Domain\Company\Entity\ValueObject;

use App\Domain\Company\Entity\ValueObject\UrlAddress;
use InvalidArgumentException;
use Tests\Unit\TestCase;

class UrlAddressTest extends TestCase
{
    /**
     * @dataProvider getValidUrlStringsCases
     */
    public function testUrlAddressValidationSuccess(string $validUrlString): void
    {
        $actualException = null;

        try {
            new UrlAddress($validUrlString);
        } catch (\Throwable $exception) {
            $actualException = $exception;
        }

        $this->assertNull($actualException);
    }

    /**
     * @return string[][]
     */
    public function getValidUrlStringsCases(): array
    {
        return [
            ['http://example.com'],
            ['https://www.example.com/'],
            ['https://example.com?key1=value1&key2=value2'],
        ];
    }

    /**
     * @dataProvider getInvalidUrlStringsCases
     */
    public function testUrlAddressValidationFail(string $invalidUrlString): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UrlAddress($invalidUrlString);
    }

    /**
     * @return string[][]
     */
    public function getInvalidUrlStringsCases(): array
    {
        return [
            ['asd qwe'],
            ['a.b'],
            ['google.c0m'],
            ['youtu_be'],
            ['site-without-scheme.domain'],
        ];
    }
}
