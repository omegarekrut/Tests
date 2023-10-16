<?php

namespace Tests\Controller\Api;

use App\Domain\User\Entity\User;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Ban\LoadBanUsers;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;

class BanControllerTest extends TestCase
{
    private const NOT_BANNED_IP = '127.0.0.1';

    private User $bannedUser;
    private User $notBannedUser;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadBanUsers::class,
            LoadMostActiveUser::class,
        ])->getReferenceRepository();

        $banUser = $referenceRepository->getReference(LoadBanUsers::BAN_USER);
        $bannedUser = $banUser->getUser();
        assert($bannedUser instanceof User);

        $notBannedUser = $referenceRepository->getReference(LoadMostActiveUser::USER_MOST_ACTIVE);
        assert($notBannedUser instanceof User);

        $this->bannedUser = $bannedUser;
        $this->notBannedUser = $notBannedUser;
    }

    public function testCheckWithoutQuery(): void
    {
        $responseEncode = json_encode(['ip' => false, 'user' => false]);
        $url = '/api/ban/check/';

        $client = $this->getBrowser();
        $client->request('GET', $url);

        $this->assertEquals($responseEncode, $this->getBrowser()->getResponse()->getContent());
    }

    public function testCheckBannedUser(): void
    {
        $responseEncode = json_encode(['ip' => false, 'user' => true]);
        $url = sprintf('/api/ban/check/?userId=%d', $this->bannedUser->getId());

        $client = $this->getBrowser();
        $client->request('GET', $url);

        $this->assertEquals($responseEncode, $this->getBrowser()->getResponse()->getContent());
    }

    public function testCheckNotBannedUser(): void
    {
        $responseEncode = json_encode(['ip' => false, 'user' => false]);
        $url = sprintf('/api/ban/check/?userId=%d', $this->notBannedUser->getId());

        $client = $this->getBrowser();
        $client->request('GET', $url);

        $this->assertEquals($responseEncode, $this->getBrowser()->getResponse()->getContent());
    }

    public function testNotBannedIp(): void
    {
        $responseEncode = json_encode(['ip' => false, 'user' => false]);
        $url = sprintf('/api/ban/check/?ip=%s', self::NOT_BANNED_IP);

        $client = $this->getBrowser();
        $client->request('GET', $url);

        $this->assertEquals($responseEncode, $this->getBrowser()->getResponse()->getContent());
    }
}
