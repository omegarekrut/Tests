<?php

namespace Repository;

use Codeception\Module;
use RuntimeException;
use Tests\Support\TransferObject\User;

class CommentRepository extends Module
{
    public function seeUserCommentsInDatabase(User $user): bool
    {
        $comment = $this->getModule('Db')->grabFromDatabase('comments', 'id', [
            'user_id' => $user->id,
            'active' => 1,
        ]);

        return $comment !== false;
    }

    public function grabActiveRecordIdByTypeWhereNotHasAnswersToComment(string $type): int
    {
        $queryString = sprintf('
            SELECT record.id FROM records AS record
            INNER JOIN comments AS comment ON comment.record_id = record.id
            WHERE
                record.type = \'%1$s\'
                AND record.active = 1
                AND record.deleted_at IS NULL
                AND record.id NOT IN (
                    SELECT record.id FROM records AS record
                    INNER JOIN comments AS comment ON comment.record_id = record.id
                    WHERE
                        record.type = \'%1$s\'
                        AND comment.parent_comment_id IS NOT NULL
                )
            LIMIT 1
        ', $type);

        $connection = $this->getModule('Db')->_getDbh();
        $query = $connection->query($queryString, \PDO::FETCH_ASSOC);
        $record = $query->fetch();

        if (!$record) {
            throw new RuntimeException('Not found records');
        }

        return $record['id'];
    }

    public function grabCommentWithAnswers(): string
    {
        $queryString = '
            SELECT parent.slug FROM comments AS comment
            INNER JOIN comments AS parent ON parent.id = comment.parent_comment_id
                LIMIT 1
        ';

        $connection = $this->getModule('Db')->_getDbh();
        $query = $connection->query($queryString, \PDO::FETCH_ASSOC);
        $comment = $query->fetch();

        if (!$comment) {
            throw new \RuntimeException('Not found comment.');
        }

        return $comment['slug'];
    }
}
