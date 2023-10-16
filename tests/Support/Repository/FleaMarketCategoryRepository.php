<?php

namespace Repository;

use Codeception\Module;
use PDO;

class FleaMarketCategoryRepository extends Module
{
    /**
     * @return string[]
     */
    public function grabAllFleaMarketCategoryIds(): array
    {
        return $this->getModule('Db')->grabColumnFromDatabase('flea_market_categories', 'id', []);
    }

    /**
     * @return string[]
     */
    public function grabNotEmptyFleaMarketCategorySlugs(): array
    {
        $queryString = '
            SELECT category.slug FROM flea_market_categories AS category
            WHERE 
                category.id IN (SELECT sub_category.parent_id FROM flea_market_categories AS sub_category); 
        ';

        /** @var PDO $connection */
        $connection = $this->getModule('Db')->_getDbh();
        $query = $connection->query($queryString);

        return $query->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return string[]
     */
    public function grabNonRootFleaMarketCategorySlugs(): array
    {
        /** @var PDO $connection */
        $connection = $this->getModule('Db')->_getDbh();
        $query = $connection->query('SELECT slug FROM flea_market_categories WHERE parent_id IS NOT NULL;');

        return $query->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return string[]
     */
    public function grabDeletableFleaMarketCategorySlugs(): array
    {
        $queryString = '
            SELECT category.slug FROM flea_market_categories AS category
            WHERE 
                category.id NOT IN (SELECT sub_category.parent_id FROM flea_market_categories AS sub_category WHERE sub_category.parent_id IS NOT NULL); 
        ';

        /** @var PDO $connection */
        $connection = $this->getModule('Db')->_getDbh();
        $query = $connection->query($queryString);

        return $query->fetchAll(PDO::FETCH_COLUMN);
    }
}
