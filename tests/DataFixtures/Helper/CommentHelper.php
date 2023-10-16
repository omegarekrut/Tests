<?php

namespace Tests\DataFixtures\Helper;

use App\Domain\Record\Common\Entity\Record;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Faker\Generator;
use Ramsey\Uuid\Uuid;

class CommentHelper
{
    private $authorHelper;
    private $generator;

    public function __construct(AuthorHelper $authorHelper, Generator $generator)
    {
        $this->authorHelper = $authorHelper;
        $this->generator = $generator;
    }

    public function addComments(AbstractFixture $fixture, Record $record): void
    {
        $commentCount = random_int(1, 10);

        for ($i = 0; $i < $commentCount; $i++) {
            $record->addComment(
                Uuid::uuid4(),
                $this->generator->regexify('[A-Za-z0-9]{20}'),
                $this->generator->realText(),
                $this->authorHelper->chooseAuthor($fixture)
            );
        }

        foreach ($record->getComments() as $comment) {
            RatingHelper::setRating($comment);
        }
    }
}
