<?php

namespace Tests\Controller\Admin\CompanyLetter\InnovationBlock;

use App\Domain\CompanyLetter\Entity\InnovationBlock;
use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\CompanyLetter\LoadInnovationBlockPreviousMonth;
use Tests\DataFixtures\ORM\User\LoadAdminUser;

class InnovationBlockControllerTest extends TestCase
{
    public function testIndex(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($user instanceof User);

        $client = $this->getBrowser()->loginUser($user);

        $client->request('GET', '/admin/innovation_block/');

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

        $client->request('GET', '/admin/innovation_block/create/');

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testEdit(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadInnovationBlockPreviousMonth::class,
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($user instanceof User);

        $innovationBlock = $referenceRepository->getReference(LoadInnovationBlockPreviousMonth::REFERENCE_NAME);
        assert($innovationBlock instanceof InnovationBlock);

        $innovationBlockId = $innovationBlock->getId();
        $innovationBlockTitle = $innovationBlock->getTitle();
        $innovationBlockData = $innovationBlock->getData();

        $client = $this->getBrowser()
            ->loginUser($user);

        $crawler = $client->request('GET', sprintf('/admin/innovation_block/%s/edit/', $innovationBlockId));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString($innovationBlockTitle, $crawler->html());
        $this->assertStringContainsString($innovationBlockData, $crawler->html());
    }

    public function testView(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadInnovationBlockPreviousMonth::class,
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($user instanceof User);

        $innovationBlock = $referenceRepository->getReference(LoadInnovationBlockPreviousMonth::REFERENCE_NAME);
        assert($innovationBlock instanceof InnovationBlock);

        $innovationBlockId = $innovationBlock->getId();
        $innovationBlockTitle = $innovationBlock->getTitle();
        $innovationBlockData = $innovationBlock->getData();

        $client = $this->getBrowser()
            ->loginUser($user);

        $crawler = $client->request('GET', sprintf('/admin/innovation_block/%s/view/', $innovationBlockId));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString($innovationBlockTitle, $crawler->html());
        $this->assertStringContainsString($innovationBlockData, $crawler->html());
    }
}
