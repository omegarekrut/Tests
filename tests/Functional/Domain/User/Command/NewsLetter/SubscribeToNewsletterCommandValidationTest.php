<?php

namespace Tests\Functional\Domain\User\Command\NewsLetter;

use App\Domain\User\Command\Subscription\SubscribeToNewsletterCommand;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\Functional\ValidationTestCase;

class SubscribeToNewsletterCommandValidationTest extends ValidationTestCase
{
    /**
     * @var User
     */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadMostActiveUser::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadMostActiveUser::USER_MOST_ACTIVE);
    }

    protected function tearDown(): void
    {
        unset($this->user);

        parent::tearDown();
    }

    /**
     * @dataProvider geNewsLetterHash
     */
    public function testInvalidHash(string $hash): void
    {
        $command = new SubscribeToNewsletterCommand($this->user, $hash);
        $this->getValidator()->validate($command);

        $this->assertNotEmpty($this->getValidator()->getLastErrors());
    }

    public function geNewsLetterHash(): array
    {
        return [
            'Invalid news letter hash' => ['invalid-news-letter-hash'],
            'Blank news letter hash' => [''],
            'Too long news letter hash' => [bin2hex(random_bytes(256))],
        ];
    }
}
