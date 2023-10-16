<?php

namespace Tests\Controller\Profile;

use App\Domain\User\Entity\User;
use Tests\Controller\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Client;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class SubscriptionsPageControllerTest extends TestCase
{
    private Client $client;
    private const SUBSCRIPTION_CONTROL_URL = '/profile/subscription-control/';

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearDatabase();

        $user = $this->loadFixture(LoadTestUser::class, User::class);

        $this->browser = $this->getBrowser();
        $this->client = $this->browser->loginUser($user);
    }

    public function testUserCanViewSubscriptionPage(): void
    {
        $this->client->request('GET', self::SUBSCRIPTION_CONTROL_URL);

        $this->assertResponseIsOk();
        $this->assertResponseContains('Управление подписками');
    }

    public function testUserCanToggleNewsletterSubscription(): void
    {
        $subscribeUrl = $this->getUrlFromComponent('switch-component', ':link-for-activate');

        $this->makeAjaxRequest($subscribeUrl);

        $this->assertResponseIsOk();
        $this->assertJsonResponseEquals(['status' => true]);

        $unsubscribeUrl = $this->getUrlFromComponent('switch-component', ':link-for-deactivate');

        $this->makeAjaxRequest($unsubscribeUrl);

        $this->assertResponseIsOk();
        $this->assertJsonResponseEquals(['status' => false]);
    }

    public function testUserCanUpdateEmailFrequency(): void
    {
        $submitUrl = $this->getUrlFromComponent('select-component', 'submit-url');

        $this->makeAjaxRequest($submitUrl . '?value=weekly');

        $this->assertResponseIsOk();
        $this->assertJsonResponseEquals(['value' => 'weekly']);
    }

    public function testInvalidEmailFrequencyReturnsBadRequest(): void
    {
        $submitUrl = $this->getUrlFromComponent('select-component', 'submit-url');

        $this->makeAjaxRequest($submitUrl . '?value=asd');

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    private function getUrlFromComponent(string $component, string $attribute): string
    {
        $crawler = $this->client->request('GET', self::SUBSCRIPTION_CONTROL_URL);

        return trim($crawler->filter($component)->attr($attribute), '\'');
    }

    private function makeAjaxRequest(string $url): void
    {
        $this->client->xmlHttpRequest('GET', $url);
    }

    private function assertResponseIsOk(): void
    {
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    private function assertResponseContains(string $string): void
    {
        $this->assertStringContainsString($string, $this->client->getResponse()->getContent());
    }

    private function assertJsonResponseEquals(array $expected): void
    {
        $this->assertEquals($expected, json_decode($this->client->getResponse()->getContent(), true));
    }
}
