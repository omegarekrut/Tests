<?php

namespace Tests\Controller\Admin\CompanyLetter\GreetingBlock;

use App\Domain\CompanyLetter\Entity\GreetingBlock;
use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\CompanyLetter\LoadGreetingBlockPreviousMonth;
use Tests\DataFixtures\ORM\User\LoadAdminUser;

class GreetingBlockControllerTest extends TestCase
{
    public function testIndex(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($user instanceof User);

        $client = $this->getBrowser()->loginUser($user);

        $client->request('GET', '/admin/company-greeting-block/');

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testCreate(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($user instanceof User);

        $client = $this->getBrowser()
            ->loginUser($user);

        $client->request('GET', '/admin/company-greeting-block/create/');

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testEdit(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadGreetingBlockPreviousMonth::class,
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($user instanceof User);

        $greetingBlock = $referenceRepository->getReference(LoadGreetingBlockPreviousMonth::REFERENCE_NAME);
        assert($greetingBlock instanceof GreetingBlock);

        $client = $this->getBrowser()
            ->loginUser($user);

        $crawler = $client->request('GET', sprintf('/admin/company-greeting-block/edit/%s/', $greetingBlock->getId()));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString($greetingBlock->getData(), $crawler->html());
    }

    public function testView(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadGreetingBlockPreviousMonth::class,
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($user instanceof User);

        $greetingBlock = $referenceRepository->getReference(LoadGreetingBlockPreviousMonth::REFERENCE_NAME);
        assert($greetingBlock instanceof GreetingBlock);

        $client = $this->getBrowser()
            ->loginUser($user);

        $crawler = $client->request('GET', sprintf('/admin/company-greeting-block/view/%s/', $greetingBlock->getId()));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString($greetingBlock->getData(), $crawler->html());
    }
}
