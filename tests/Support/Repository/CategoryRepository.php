<?php

namespace Repository;

use Codeception\Module;
use PDO;

class CategoryRepository extends Module
{
    /**
     * @return string[]
     */
    public function grabAllCategoryIds(): array
    {
        return $this->getModule('Db')->grabColumnFromDatabase('categories', 'id', []);
    }

    /**
     * @return string[]
     */
    public function grabAllRootCategoryIds(): array
    {
        return $this->getModule('Db')->grabColumnFromDatabase('categories', 'id', [
            'parent_id' => null,
        ]);
    }

    /**
     * @return string[]
     */
    public function grabNotEmptyCategoryIds(): array
    {
        return $this->getModule('Db')->grabColumnFromDatabase('categories', 'id', [
            'record_count !=' => 0,
        ]);
    }

    /**
     * @return string[]
     */
    public function grabNonRootCategoryIds(): array
    {
        /** @var PDO $connection */
        $connection = $this->getModule('Db')->_getDbh();
        $query = $connection->query('SELECT id FROM categories WHERE parent_id IS NOT NULL;');

        return $query->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return string[]
     */
    public function grabDeletableCategoryIds(): array
    {
        $queryString = '
            SELECT category.id FROM categories AS category
                LEFT JOIN categories AS child_category ON category.id = child_category.parent_id 
            WHERE 
                category.record_count = 0 
                AND child_category.parent_id IS NULL; 
        ';

        /** @var PDO $connection */
        $connection = $this->getModule('Db')->_getDbh();
        $query = $connection->query($queryString);
        $categoryIds = $query->fetchAll(PDO::FETCH_COLUMN);

        return $categoryIds;
    }

    public function grabSubCategoryIdBySlug(string $slug): int
    {
        $parentCategoryId = (int) $this->getModule('Db')->grabFromDatabase('categories', 'id', [
            'url_title' => $slug,
        ]);

        return (int) $this->getModule('Db')->grabFromDatabase('categories', 'id', [
            'parent_id' => $parentCategoryId,
        ]);
    }

    public function grabRandomSubCategoryIdBySlug(string $slug): int
    {
        $parentCategoryId = $this->getModule('Db')->grabFromDatabase('categories', 'id', [
            'url_title' => $slug,
        ]);

        $subCategoryIds = $this->getModule('Db')->grabColumnFromDatabase('categories', 'id', [
            'parent_id' => $parentCategoryId,
        ]);

        $index = rand(0, count($subCategoryIds) - 1);

        return $subCategoryIds[$index];
    }
}
