<?php

namespace Tests\Controller\Admin\CompanyLetter\InnovationBlock;

use App\Domain\CompanyLetter\Entity\InnovationBlock;
use App\Domain\User\Entity\User;
use App\Util\ImageStorage\Image;
use Carbon\Carbon;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\CompanyLetter\LoadInnovationBlockPreviousMonth;
use Tests\DataFixtures\ORM\User\LoadAdminUser;

class InnovationBlockCrudControllerTest extends TestCase
{
    /**
     * @todo После выполнения задачи https://resolventa.atlassian.net/browse/FS-3218 добавить testCreateInnovationBlock
     */
    public function testEditInnovationBlock(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadInnovationBlockPreviousMonth::class,
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($user instanceof User);

        $innovationBlock = $referenceRepository->getReference(LoadInnovationBlockPreviousMonth::REFERENCE_NAME);
        assert($innovationBlock instanceof InnovationBlock);

        $browser = $this->getBrowser()
            ->loginUser($user);

        $browser->request('GET', sprintf('/admin/innovation_block/%s/edit/', $innovationBlock->getId()));

        $title = 'New InnovationBlock title';
        $data = 'New InnovationBlock data';
        $image = new Image('image_new.jpg');
        $startAt = Carbon::now()->subDay();
        $finishAt = Carbon::now();

        $browser->submitForm('Сохранить', [
            'innovationBlock[title]' => $title,
            'innovationBlock[data]' => $data,
            'innovationBlock[image]' => $image,
            'innovationBlock[startAt][year]' => $startAt->year,
            'innovationBlock[startAt][month]' => $startAt->month,
            'innovationBlock[startAt][day]' => $startAt->day,
            'innovationBlock[finishAt][year]' => $finishAt->year,
            'innovationBlock[finishAt][month]' => $finishAt->month,
            'innovationBlock[finishAt][day]' => $finishAt->day,
        ]);

        $this->assertTrue($browser->getResponse()->isRedirect('/admin/innovation_block/'));

        $indexPage = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', 'Письмо для компаний отредактировано', $indexPage->html());

        $this->assertStringContainsString($title, $indexPage->html());
    }
}
