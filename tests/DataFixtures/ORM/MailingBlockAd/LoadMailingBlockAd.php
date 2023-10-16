<?php

namespace Tests\DataFixtures\ORM\MailingBlockAd;

use App\Domain\MailingBlockAd\Entity\MailingBlockAd;
use Carbon\Carbon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\Helper\MediaHelper;

class LoadMailingBlockAd extends Fixture implements FixtureInterface
{
    public const REFERENCE_NAME = 'load-mailing-block-ad';

    private MediaHelper $mediaHelper;

    public function __construct(MediaHelper $mediaHelper)
    {
        $this->mediaHelper = $mediaHelper;
    }

    public function load(ObjectManager $manager): void
    {
        $id = Uuid::uuid4();
        $title = 'Сегодня ерш предпочел исключительно морш';
        $data = '[b]Эх, а какая стояла в эту субботу погода. Море, солнце, штиль, и посередине всей этой красоты одинокая лодка с двумя рыбаками, двумя рыбамями посредине.';
        $image = $this->mediaHelper->createImage();
        $startAt = Carbon::now()->subWeek();
        $finishAt = Carbon::now()->addWeek()->addWeek();

        $mailingBlockAd = new MailingBlockAd(
            $id,
            $title,
            $data,
            $image,
            $startAt,
            $finishAt
        );
        $this->addReference(self::REFERENCE_NAME, $mailingBlockAd);

        $manager->persist($mailingBlockAd);
        $manager->flush();
    }
}
