<?php

namespace Repository;

use Codeception\Module;
use PDO;

class RegionRepository extends Module
{
    /**
     * @return string
     */
    public function getRandomRegionId(): string
    {
        $connection = $this->getModule('Db')->_getDbh();

        $regionIds = $connection->query('SELECT id FROM country_regions')->fetchAll(PDO::FETCH_COLUMN);

        return $regionIds[array_rand($regionIds)];
    }
}
