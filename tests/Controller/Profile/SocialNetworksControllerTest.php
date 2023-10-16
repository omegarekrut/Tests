<?php

namespace Tests\Controller\Profile;

use App\Domain\User\Entity\User;
use Tests\Controller\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Client;
use Tests\DataFixtures\ORM\User\LoadUserWithLinkedAccount;

class SocialNetworksControllerTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearDatabase();

        $user = $this->loadFixture(LoadUserWithLinkedAccount::class, User::class);

        $this->browser = $this->getBrowser();
        $this->client = $this->browser->loginUser($user);
    }

    public function testAjaxRequestToAllowShowSocial(): void
    {
        $this->makeAjaxRequest('/profile/social-network/show/');

        $this->assertResponseIsOk();
    }

    public function testRequestToAllowShowSocial(): void
    {
        $this->makeRequest('/profile/social-network/show/');

        $this->assertRedirectFollowedWithSuccessMessage('Другие пользователи теперь могут видеть Ваши профили в социальных сетях.');
    }

    public function testAjaxRequestToDenyShowSocial(): void
    {
        $this->makeAjaxRequest('/profile/social-network/hide/');

        $this->assertResponseIsOk();
    }

    public function testRequestToDenyShowSocial(): void
    {
        $this->makeRequest('/profile/social-network/hide/');

        $this->assertRedirectFollowedWithSuccessMessage('Другие пользователи больше не могут видеть Ваши профили в социальных сетях.');
    }

    public function testUserCanSeeAndRemoveLinkedCompanies(): void
    {
        $page = $this->client->request('GET', '/profile/edit/social-networks/');
        $link = $page->filter('[title="Удалить связь"]')->first()->link();
        $this->client->click($link);

        $viewPage = $this->client->followRedirect();

        $this->assertSeeAlertInPageContent('success', 'Связь успешно удалена', $viewPage->html());
    }

    private function makeAjaxRequest(string $url): void
    {
        $this->client->xmlHttpRequest('GET', $url);
    }

    private function makeRequest(string $url): void
    {
        $this->client->request('GET', $url);
        $this->client->followRedirect();
    }

    private function assertResponseIsOk(): void
    {
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    private function assertRedirectFollowedWithSuccessMessage(string $message): void
    {
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSeeAlertInPageContent('success', $message, $this->client->getResponse()->getContent());
    }
}