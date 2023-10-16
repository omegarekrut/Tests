<?php

namespace Repository;

use Codeception\Module;

class TidingsRepository extends Module
{
    public function grabCommentedTidingsIdByAuthorGroup(string $authorGroup): int
    {
        $queryString = sprintf('
            SELECT record.id FROM records AS record
            INNER JOIN users AS user ON record.user_id = user.id
            WHERE
                user.`group` = \'%s\'
                AND record.type = \'tidings\'
                AND record.comment_count > 0
                LIMIT 1
        ', $authorGroup);

        $connection = $this->getModule('Db')->_getDbh();
        $query = $connection->query($queryString, \PDO::FETCH_ASSOC);
        $record = $query->fetch();

        if (!$record) {
            throw new \RuntimeException('Not found tidings by author group.');
        }

        return $record['id'];
    }

    public function grabActiveTidingsId(): int
    {
        return $this->getModule('Db')->grabFromDatabase('records', 'id', [
            'active' => 1,
            'deleted_at' => null,
            'type' => 'tidings',
        ]);
    }
}
