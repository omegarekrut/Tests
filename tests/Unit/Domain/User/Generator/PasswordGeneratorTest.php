<?php

namespace Tests\Unit\Domain\User\Generator;

use App\Domain\User\Generator\PasswordGenerator;
use Tests\Unit\TestCase;

/**
 * @group user-generator
 * @group oauth
 */
class PasswordGeneratorTest extends TestCase
{
    public function testGeneration(): void
    {
        $generator = new PasswordGenerator();
        $password1 = $generator->generate();
        $password2 = $generator->generate();

        $this->assertNotEquals($password1, $password2);
        $this->assertEquals(7, mb_strlen($password1));
    }
}
