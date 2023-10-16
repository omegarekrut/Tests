<?php

namespace Tests\DataFixtures\ORM\CompanyLetter;

use App\Domain\CompanyLetter\Entity\CompanyLetter;
use App\Domain\CompanyLetter\Entity\GreetingBlock;
use App\Domain\CompanyLetter\Entity\InnovationBlock;
use App\Domain\CompanyLetter\Entity\ValueObject\CompanyLetterPeriod;
use Carbon\Carbon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Ramsey\Uuid\Uuid;

class LoadCompanyLetterForPreviousMonth extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'company-letter-sent';

    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $greetingBlock = $this->getReference(LoadGreetingBlockPreviousMonth::REFERENCE_NAME);
        assert($greetingBlock instanceof GreetingBlock);

        $innovationBlock = $this->getReference(LoadInnovationBlockPreviousMonth::REFERENCE_NAME);
        assert($innovationBlock instanceof InnovationBlock);

        $innovationBlocks = [$innovationBlock];
        $innovationBlocks = new ArrayCollection($innovationBlocks);

        $companyLetter = new CompanyLetter(
            Uuid::uuid4(),
            CompanyLetterPeriod::createPreviousCompanyLetterPeriod(),
            1,
            $greetingBlock,
            $innovationBlocks
        );

        $companyLetter->setSendingDate(
            Carbon::instance($companyLetter->getPeriodDate())->addDay()
        );
        $companyLetter->setNumberOfRecipients(15);

        $this->addReference(self::REFERENCE_NAME, $companyLetter);

        $manager->persist($companyLetter);
        $manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            LoadGreetingBlockPreviousMonth::class,
            LoadInnovationBlockPreviousMonth::class,
        ];
    }
}
