<?php

namespace Repository;

use Codeception\Module;
use RuntimeException;
use Tests\Support\TransferObject\User;

class RecordRepository extends Module
{
    public function seeUserRecordsInDatabase(User $user, string $recordType): bool
    {
        $record = $this->getModule('Db')->grabFromDatabase('records', 'id', [
            'type' => $recordType,
            'user_id' => $user->id,
            'active' => 1,
            'deleted_at' => null,
        ]);

        return $record !== false;
    }

    public function grabActiveRecordIdByTypeCreatedNotBannedUser(string $type): int
    {
        $queryString = sprintf('
            SELECT record.id FROM records AS record
            INNER JOIN users AS user ON record.user_id = user.id
            LEFT JOIN ban_user ON ban_user.user_id = user.id
            WHERE
                user.`group` = \'user\'
                AND record.type = \'%s\'
                AND record.active = 1
                AND record.deleted_at IS NULL
                AND ban_user.id IS NULL
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

    public function grabActiveVideoIdWithImageCreatedByNotBannedUser(): int
    {
        $queryString = '
            SELECT record.id FROM records AS record
            INNER JOIN users AS user ON record.user_id = user.id
            LEFT JOIN ban_user ON ban_user.user_id = user.id
            RIGHT JOIN videos AS video ON video.id = record.id
            WHERE
                user.`group` = \'user\'
                AND record.active = 1
                AND record.deleted_at IS NULL
                AND ban_user.id IS NULL
                AND video.image != ""
            LIMIT 1
        ';

        $connection = $this->getModule('Db')->_getDbh();
        $query = $connection->query($queryString, \PDO::FETCH_ASSOC);
        $record = $query->fetch();

        if (!$record) {
            throw new RuntimeException('Not found records');
        }

        return $record['id'];
    }

    public function grabActiveRecordIdByType(string $type): int
    {
        return $this->getModule('Db')->grabFromDatabase('records', 'id', [
            'active' => 1,
            'deleted_at' => null,
            'type' => $type,
        ]);
    }

    /**
     * @return mixed[]
     */
    public function grabActiveRecordByType(string $type): array
    {
        $queryString = ' 
            SELECT *
            FROM
                `records` as `record`
            WHERE record.type = \'%s\'
                AND record.active = 1
                AND record.deleted_at IS NULL
            LIMIT 1;
        ';

        $connection = $this->getModule('Db')->_getDbh();
        $query = $connection->query(sprintf($queryString, $type));

        return $query->fetch();
    }
}
