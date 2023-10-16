<?php

namespace Tests\Controller\WeeklyLetter;

use App\Domain\WeeklyLetter\Entity\WeeklyLetter;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\WeeklyLetter\LoadWeeklyLetterBefore;
use Tests\DataFixtures\ORM\WeeklyLetter\LoadWeeklyLetterCurrent;

final class WeeklyLetterAccessControllerTest extends TestCase
{
    public function testGuestCanViewLastWeeklyLetter(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadWeeklyLetterBefore::class,
            LoadWeeklyLetterCurrent::class,
        ])->getReferenceRepository();

        $currentWeeklyLetter = $referenceRepository->getReference(LoadWeeklyLetterCurrent::REFERENCE_NAME);
        assert($currentWeeklyLetter instanceof WeeklyLetter);

        $browser = $this->getBrowser();

        $page = $browser->request('GET', '/weekly-letter/last/');

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString(
            sprintf('№%d.', $currentWeeklyLetter->getNumber()),
            $page->filter('h1')->text()
        );
    }
}
