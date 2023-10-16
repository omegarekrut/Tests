<?php

namespace Repository;

use Codeception\Module;
use PDO;

class ArticleRepository extends Module
{
    public function findArticleWithContentsGenerationPossibleId(): ?int
    {
        $queryString = " 
            SELECT 
                `record`.`id` 
            FROM 
                `records` AS `record` 
                INNER JOIN `articles` AS `article` ON `article`.`id` = `record`.`id` 
            WHERE 
                `record`.`type` = 'article' 
                AND `record`.`data` RLIKE '([[][Hh][2][]])(.*)([[][Hh][234][]])(.*)([[][Hh][234][]])'
                AND `record`.`deleted_at` IS NULL 
            LIMIT 1; 
        ";

        /** @var PDO $connection */
        $connection = $this->getModule('Db')->_getDbh();

        $query = $connection->query($queryString, PDO::FETCH_COLUMN, 0);

        return $query->fetchColumn();
    }

    public function grabCommentedArticleIdByAuthorGroup(string $authorGroup): int
    {
        $queryString = sprintf('
            SELECT record.id FROM records AS record
            INNER JOIN users AS user ON record.user_id = user.id
            INNER JOIN comments AS comment ON record.id = comment.record_id
            WHERE
                comment.`user_id` IS NOT NULL
                AND user.`group` = \'%s\'
                AND record.type = \'article\'
                AND record.deleted_at IS NULL
                AND comment.parent_comment_id IS NOT NULL
                AND record.comment_count > 0
                LIMIT 1
        ', $authorGroup);

        $connection = $this->getModule('Db')->_getDbh();
        $query = $connection->query($queryString, \PDO::FETCH_ASSOC);
        $record = $query->fetch();

        if (!$record) {
            throw new \RuntimeException('Not found article by author group with comments.');
        }

        return $record['id'];
    }
}
