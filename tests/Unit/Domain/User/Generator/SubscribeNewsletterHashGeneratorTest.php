<?php

namespace Tests\Unit\Domain\User\Generator;

use App\Domain\User\Generator\SubscribeNewsletterHashGenerator;
use Tests\Unit\TestCase;

/**
 * @group user-generator
 */
class SubscribeNewsletterHashGeneratorTest extends TestCase
{
    private const USER_ID = 1;
    private const VALID_HASH = '5b7a6f645e83099ce094f03cae7718e9';
    private const TEST_SUBSCRIBE_SALT = 'TEST_SUBSCRIBE_SALT';

    /** @var SubscribeNewsletterHashGenerator */
    private $generator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = new SubscribeNewsletterHashGenerator(self::TEST_SUBSCRIBE_SALT);
    }

    public function testGeneration(): void
    {
        $this->assertEquals(self::VALID_HASH, $this->generator->generate(self::USER_ID));
    }
}
