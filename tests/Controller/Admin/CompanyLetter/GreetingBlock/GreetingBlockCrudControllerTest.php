<?php

namespace Tests\Controller\Admin\CompanyLetter\GreetingBlock;

use App\Doctrine\NoMagic\IgnoreDoctrineFiltersAndSoftDeletableEnvironment;
use App\Domain\CompanyLetter\Entity\GreetingBlock;
use App\Domain\CompanyLetter\Repository\GreetingBlockRepository;
use App\Domain\User\Entity\User;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\CompanyLetter\LoadGreetingBlockPreviousMonth;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\Traits\FakerFactoryTrait;

class GreetingBlockCrudControllerTest extends TestCase
{
    use FakerFactoryTrait;

    public function testEditCompanyGreetingBlockAsModerator(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadGreetingBlockPreviousMonth::class,
            LoadModeratorUser::class,
        ])->getReferenceRepository();

        $greetingBlock = $referenceRepository->getReference(LoadGreetingBlockPreviousMonth::REFERENCE_NAME);
        assert($greetingBlock instanceof GreetingBlock);

        $moderator = $referenceRepository->getReference(LoadModeratorUser::REFERENCE_NAME);
        assert($moderator instanceof User);

        $browser = $this->getBrowser()
            ->loginUser($moderator);

        $crawler = $browser->request('GET', sprintf('/admin/company-greeting-block/edit/%s/', $greetingBlock->getId()));

        $data = $this->getFaker()->realText(200);

        $tidingFormButton = $crawler->selectButton('Сохранить');

        $form = $tidingFormButton->form([], 'POST');

        $browser->submit($form, [
            'companyGreetingBlock[data]' => $data,
            'companyGreetingBlock[startAt][year]' => 2022,
            'companyGreetingBlock[startAt][month]' => 2,
            'companyGreetingBlock[startAt][day]' => 2,
            'companyGreetingBlock[finishAt][year]' => 2022,
            'companyGreetingBlock[finishAt][month]' => 4,
            'companyGreetingBlock[finishAt][day]' => 4,
        ]);

        $this->assertTrue($browser->getResponse()->isRedirect('/admin/company-greeting-block/'));

        $ignoreDoctrineFiltersAndSoftDeletableEnvironment = $this->getContainer()->get(IgnoreDoctrineFiltersAndSoftDeletableEnvironment::class);
        ($ignoreDoctrineFiltersAndSoftDeletableEnvironment)(fn() => $referenceRepository->getManager()->refresh($greetingBlock));

        $this->assertEquals($data, $greetingBlock->getData());
        $this->assertEquals('2022-02-02 00:00:00', $greetingBlock->getStartAt()->format('Y-m-d H:i:s'));
        $this->assertEquals('2022-04-04 00:00:00', $greetingBlock->getFinishAt()->format('Y-m-d H:i:s'));
    }

    public function testCreateCompanyGreetingBlockAsAdmin(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($user instanceof User);

        $browser = $this->getBrowser()
            ->loginUser($user);

        $crawler = $browser->request('GET', sprintf('/admin/company-greeting-block/create/'));

        $data = $this->getFaker()->realText();

        $tidingFormButton = $crawler->selectButton('Сохранить');

        $form = $tidingFormButton->form([], 'POST');

        $browser->submit($form, [
            'companyGreetingBlock[data]' => $data,
            'companyGreetingBlock[startAt][year]' => 2022,
            'companyGreetingBlock[startAt][month]' => 2,
            'companyGreetingBlock[startAt][day]' => 2,
            'companyGreetingBlock[finishAt][year]' => 2022,
            'companyGreetingBlock[finishAt][month]' => 4,
            'companyGreetingBlock[finishAt][day]' => 4,
        ]);

        $this->assertTrue($browser->getResponse()->isRedirect('/admin/company-greeting-block/'));

        $ignoreDoctrineFiltersAndSoftDeletableEnvironment = $this->getContainer()->get(IgnoreDoctrineFiltersAndSoftDeletableEnvironment::class);
        $repository = $this->getContainer()->get(GreetingBlockRepository::class);

        $companyMailingBlock = ($ignoreDoctrineFiltersAndSoftDeletableEnvironment)(fn() => $repository->getAllCompanyGreetingBlocks());

        $this->assertEquals($data, $companyMailingBlock[0]->getData());
        $this->assertEquals('2022-02-02 00:00:00', $companyMailingBlock[0]->getStartAt()->format('Y-m-d H:i:s'));
        $this->assertEquals('2022-04-04 00:00:00', $companyMailingBlock[0]->getFinishAt()->format('Y-m-d H:i:s'));
    }
}
