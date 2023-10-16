<?php

namespace Repository;

use Codeception\Module;
use PDO;
use PDOStatement;
use Tests\Support\Repository\Exception\CompanyArticleNotFoundException;

class CompanyArticleRepository extends Module
{
    public function grabCompanyArticleIdByAuthorId(int $userId): int
    {
        $queryString = '
            SELECT `company_article`.`id`
            FROM `company_articles` AS `company_article`
            LEFT JOIN `records` as `record` on `company_article`.`id` = `record`.`id`
            WHERE `record`.`user_id` = :userId
            LIMIT 1;
        ';

        $connection = $this->getModule('Db')->_getDbh();
        assert($connection instanceof PDO);

        $query = $connection->prepare($queryString);
        $query->bindParam('userId', $userId);
        $query->execute();

        $article = $query->fetch(PDO::FETCH_ASSOC);

        return $article['id'];
    }

    public function grabHiddenCompanyArticleId(): int
    {
        $queryString = '
            SELECT company_article.id
            FROM company_articles AS company_article
                INNER JOIN records as record on company_article.id = record.id
            WHERE record.active = 0
            LIMIT 1;
        ';

        $query = $this->createQuery($queryString);
        $queryResult = $query->fetch(PDO::FETCH_ASSOC);

        if (!$queryResult) {
            throw new CompanyArticleNotFoundException();
        }

        return $queryResult['id'];
    }

    public function grabActiveCompanyArticleIdOwnedByNotBannedUser(): int
    {
        $queryString = sprintf('
            SELECT record.id FROM records AS record
            INNER JOIN company_articles AS company_article ON record.id = company_article.id
            INNER JOIN users AS user ON record.user_id = user.id
            LEFT JOIN ban_user ON ban_user.user_id = user.id
            WHERE
                record.active = 1
                AND record.deleted_at IS NULL
                AND ban_user.id IS NULL
            LIMIT 1
        ');

        $query = $this->createQuery($queryString);
        $queryResult = $query->fetch(PDO::FETCH_ASSOC);

        if (!$queryResult) {
            throw new CompanyArticleNotFoundException();
        }

        return $queryResult['id'];
    }

    public function grabCompanyArticleOwnerIdByCompanyArticleId(int $companyArticleId): int
    {
        $queryString = '
            SELECT c.user_id
            FROM company_articles AS company_article
                INNER JOIN records as record on company_article.id = record.id
                INNER JOIN companies c on record.company_author_id = c.id
            WHERE company_article.id = :companyArticleId
            LIMIT 1;
        ';

        $connection = $this->getModule('Db')->_getDbh();
        assert($connection instanceof PDO);

        $query = $connection->prepare($queryString);
        $query->bindParam('companyArticleId', $companyArticleId);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC)['user_id'];
    }

    private function createQuery(string $queryString): PDOStatement
    {
        $connection = $this->getModule('Db')->_getDbh();
        assert($connection instanceof PDO);

        return $connection->query($queryString);
    }
}
