<?php

namespace Tests\Unit\Mock\Entity;

use App\Domain\Comment\Collection\CommentCollection;
use App\Domain\Record\Common\Entity\Record;

class RecordMock extends Record
{
    private CommentCollection $answers;
    public function isAllowSemanticLinks(): bool
    {
        return true;
    }

    public function setAnswersCollection(CommentCollection $answersCollection): void
    {
        $this->answers = $answersCollection;
    }

    public function getCommentsWithAnswers(): CommentCollection
    {
        return $this->answers;
    }
}
