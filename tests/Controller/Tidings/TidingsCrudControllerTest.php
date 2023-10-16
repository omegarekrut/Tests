<?php

namespace Tests\Controller\Tidings;

use App\Doctrine\NoMagic\IgnoreDoctrineFiltersAndSoftDeletableEnvironment;
use App\Domain\Record\Tidings\Entity\Tidings;
use App\Domain\Region\Entity\Region;
use App\Domain\User\Entity\User;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Record\Tidings\LoadTidingsForSemanticLinks;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Traits\FakerFactoryTrait;

class TidingsCrudControllerTest extends TestCase
{
    use FakerFactoryTrait;

    public function testEditTidingAsOwner(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTidingsForSemanticLinks::class,
        ])->getReferenceRepository();

        $tiding = $referenceRepository->getReference(LoadTidingsForSemanticLinks::REFERENCE_NAME);
        assert($tiding instanceof Tidings);

        $browser = $this->getBrowser()
            ->loginUser($tiding->getAuthor());

        $crawler = $browser->request('GET', sprintf('/tidings/%d/edit/', $tiding->getId()));

        $title = $this->getFaker()->realText(100);
        $text = $this->getFaker()->realText(200);
        $fishingTime = $this->getFaker()->realText(100);
        $place = $this->getFaker()->realText(100);
        $tackles = $this->getFaker()->realText(100);
        $catch = $this->getFaker()->realText(100);
        $weather = $this->getFaker()->realText(100);
        $regionId = Region::OTHER_REGION_ID;

        $tidingFormButton = $crawler->selectButton('Сохранить');

        $form = $tidingFormButton->form([], 'POST');

        $browser->submit($form, [
            'tidings[title]' => $title,
            'tidings[text]' => $text,
            'tidings[fishingTime]' => $fishingTime,
            'tidings[place]' => $place,
            'tidings[tackles]' => $tackles,
            'tidings[catch]' => $catch,
            'tidings[weather]' => $weather,
            'tidings[regionId]' => $regionId,
        ]);

        $this->assertTrue($browser->getResponse()->isRedirect(sprintf('/tidings/view/%d/', $tiding->getId())));

        $viewPage = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', 'Запись успешно обновлена.', $viewPage->html());

        $this->assertStringContainsString($title, $viewPage->filter('h1')->first()->text());
        $this->assertStringContainsString($text, $viewPage->filter('.articleFS__content')->first()->text());

        $tidingsDetails = $viewPage->filter('.tidings-details .tidings-details__description');

        $this->assertCount(5, $tidingsDetails);

        foreach ($tidingsDetails as $tidingsDetailsDomElement) {
            $this->assertContains($tidingsDetailsDomElement->textContent, [
                $fishingTime,
                $place,
                $tackles,
                $catch,
                $weather,
            ]);
        }
    }

    public function testHideTidingAsModerator(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadModeratorUser::class,
            LoadTidingsForSemanticLinks::class,
        ])->getReferenceRepository();

        $moderator = $referenceRepository->getReference(LoadModeratorUser::REFERENCE_NAME);
        assert($moderator instanceof User);

        $tiding = $referenceRepository->getReference(LoadTidingsForSemanticLinks::REFERENCE_NAME);
        assert($tiding instanceof Tidings);

        $browser = $this->getBrowser()
            ->loginUser($moderator);

        $browser->request('GET', sprintf('/tidings/delete/%d/', $tiding->getId()));

        $this->assertTrue($browser->getResponse()->isRedirect('/tidings/'));

        $ignoreDoctrineFiltersAndSoftDeletableEnvironment = $this->getContainer()->get(IgnoreDoctrineFiltersAndSoftDeletableEnvironment::class);
        ($ignoreDoctrineFiltersAndSoftDeletableEnvironment)(fn() => $referenceRepository->getManager()->refresh($tiding));

        $this->assertTrue($tiding->isHidden());
    }

    public function testCreateTiding(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        assert($user instanceof User);

        $browser = $this->getBrowser()
            ->loginUser($user);

        $crawler = $browser->request('GET', '/tidings/create/');

        $title = $this->getFaker()->realText(100);
        $text = $this->getFaker()->realText(200);
        $regionId = Region::OTHER_REGION_ID;

        $tidingFormButton = $crawler->selectButton('Опубликовать');

        $form = $tidingFormButton->form([], 'POST');

        $browser->submit($form, [
            'tidings[title]' => $title,
            'tidings[text]' => $text,
            'tidings[regionId]' => $regionId,
        ]);

        $this->assertTrue($browser->getResponse()->isRedirect('/tidings/'));

        $viewPage = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', 'Ваша запись успешно добавлена.', $viewPage->html());
    }
}
