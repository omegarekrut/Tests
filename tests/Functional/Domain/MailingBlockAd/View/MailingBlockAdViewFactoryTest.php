<?php

namespace Tests\Functional\Domain\MailingBlockAd\View;

use App\Domain\MailingBlockAd\View\MailingBlockAdViewFactory;
use Tests\DataFixtures\ORM\MailingBlockAd\LoadMailingBlockAd;
use Tests\Functional\TestCase;

class MailingBlockAdViewFactoryTest extends TestCase
{
    public function testBbcode(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadMailingBlockAd::class,
        ])->getReferenceRepository();

        $mailingBlockAd = $referenceRepository->getReference(LoadMailingBlockAd::REFERENCE_NAME);
        $mailingBlockAdViewFactory = $this->getContainer()->get(MailingBlockAdViewFactory::class);

        $mailingBlockAdView = $mailingBlockAdViewFactory->create($mailingBlockAd);

        $this->assertEquals($mailingBlockAdView->data, '<b>Эх, а какая стояла в эту субботу погода. Море, солнце, штиль, и посередине всей этой красоты одинокая лодка с двумя рыбаками, двумя рыбамями посредине.</b>');
    }
}
