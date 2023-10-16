<?php

namespace Tests\Unit\Domain\User\Normalizer;

use App\Domain\User\Entity\ValueObject\EmailFrequency;
use App\Domain\User\Normalizer\EmailFrequencyNormalizer;
use Tests\Unit\TestCase;

/**
 * @group EmailFrequency
 */
class EmailFrequencyNormalizerTest extends TestCase
{
    private EmailFrequencyNormalizer $emailFrequencyNormalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->emailFrequencyNormalizer = new EmailFrequencyNormalizer();
    }

    protected function tearDown(): void
    {
        unset($this->emailFrequencyNormalizer);

        parent::tearDown();
    }

    public function testNormalize(): void
    {
        $emailFrequency = EmailFrequency::weekly();

        $expectedNormalizedData = ['key' => 'weekly', 'name' => 'Еженедельно'];

        $this->assertEquals($expectedNormalizedData, $this->emailFrequencyNormalizer->normalize($emailFrequency));
    }

    public function testNormalizeCollectionWithEmptyArray(): void
    {
        $emailFrequencyList = [];

        $expectedNormalizedData = [];

        $this->assertEquals($expectedNormalizedData, $this->emailFrequencyNormalizer->normalizeCollection($emailFrequencyList));
    }

    public function testNormalizeCollection(): void
    {
        $expectedNormalizedData = [
            ['key' => 'hourly', 'name' => 'Каждый час'],
            ['key' => 'daily', 'name' => 'Ежедневно'],
            ['key' => 'weekly', 'name' => 'Еженедельно'],
            ['key' => 'never', 'name' => 'Никогда'],
        ];

        $this->assertEquals($expectedNormalizedData, $this->emailFrequencyNormalizer->normalizeCollection(EmailFrequency::values()));
    }

    /**
     * @dataProvider getEmailFrequencies
     */
    public function testAnyEmailFrequencyCanBeNormalized(EmailFrequency $emailFrequency): void
    {
        $unexpectedException = null;

        try {
            $this->emailFrequencyNormalizer->normalize($emailFrequency);
        } catch (\Throwable $exception) {
            $unexpectedException = $exception;
        }

        $this->assertNull($unexpectedException);
    }

    public function getEmailFrequencies(): \Generator
    {
        foreach (EmailFrequency::values() as $emailFrequency) {
            yield [$emailFrequency];
        }
    }
}
