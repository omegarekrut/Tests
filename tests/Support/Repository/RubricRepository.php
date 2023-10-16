<?php

namespace Repository;

use Codeception\Module;
use PDO;
use PDOStatement;

class RubricRepository extends Module
{
    /**
     * @return mixed[] Format ['id' => 'uuid', 'name' => 'name']
     */
    public function findRubricIdAndNameWithoutCompany(): array
    {
        $queryString = '
            SELECT `rubric`.id, `rubric`.`name`
            FROM `rubric` as `rubric`
            LEFT JOIN `company_rubrics` AS `company_rubric` ON `rubric`.`id` = `company_rubric`.`rubric_id`
            WHERE `company_rubric`.`company_id` is null
        ';

        return $this->createQuery($queryString)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @return mixed[] Format ['id' => 'uuid', 'name' => 'name', 'slug' => 'slug']
     */
    public function findRubricWithCompany(): array
    {
        $queryString = '
            SELECT `rubric`.id, `rubric`.`name`, `rubric`.`slug`
            FROM `rubric` as `rubric`
            INNER JOIN `company_rubrics` AS `company_rubric` ON `rubric`.`id` = `company_rubric`.`rubric_id`
            WHERE `company_rubric`.`company_id` is not null
            GROUP BY `rubric`.id
            HAVING COUNT(`rubric`.`slug`) > 20
        ';

        return $this->createQuery($queryString)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @return mixed[] Format ['id' => 'uuid', 'name' => 'name', 'slug' => 'slug']
     */
    public function findRandomCompanyRubric(): array
    {
        $queryString = '
            SELECT `rubric`.id, `rubric`.`name`, `rubric`.`slug`
            FROM `rubric` as `rubric`
            ORDER BY RAND()
            LIMIT 1
        ';

        return $this->createQuery($queryString)->fetch(PDO::FETCH_ASSOC);
    }

    private function createQuery(string $queryString): PDOStatement
    {
        $connection = $this->getModule('Db')->_getDbh();
        assert($connection instanceof PDO);

        return $connection->query($queryString);
    }
}
