<?php

namespace Repository;

use Codeception\Module;
use PDO;

class WaterRepository extends Module
{
    /**
     * @return string[]
     */
    public function grabAllWaterNames(): array
    {
        $queryString = 'SELECT name FROM waters';

        $connection = $this->getModule('Db')->_getDbh();
        assert($connection instanceof PDO);

        $query = $connection->query($queryString);

        return array_column($query->fetchAll(PDO::FETCH_ASSOC), 'name');
    }

    /**
     * @return string[]
     */
    public function grabShownWaterNames(): array
    {
        $queryString = '
            SELECT w.name
            FROM waters AS w
            INNER JOIN gauging_stations AS gs ON (w.id = gs.water_id AND gs.hidden = "0")
            GROUP BY w.id
        ';

        $connection = $this->getModule('Db')->_getDbh();
        assert($connection instanceof PDO);

        $query = $connection->query($queryString);

        return array_column($query->fetchAll(PDO::FETCH_ASSOC), 'name');
    }

    /**
     * @return string[]
     */
    public function grabAllWatersIdAndName(): array
    {
        $queryString = 'SELECT id, name FROM waters';

        $connection = $this->getModule('Db')->_getDbh();
        assert($connection instanceof PDO);

        $query = $connection->query($queryString);

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return string[]
     */
    public function grabAllWaterNamesExceptWaterWithId(string $waterId): array
    {
        $queryString = 'SELECT name FROM waters WHERE id != \''.$waterId.'\'';

        $connection = $this->getModule('Db')->_getDbh();
        assert($connection instanceof PDO);

        $query = $connection->query($queryString);

        return array_column($query->fetchAll(PDO::FETCH_ASSOC), 'name');
    }

    public function resetParentIdForWater(string $waterId): void
    {
        $queryString = '
            UPDATE waters
            SET parent_water_id = null
            WHERE id = :waterId
        ';

        $connection = $this->getModule('Db')->_getDbh();
        assert($connection instanceof PDO);

        $query = $connection->prepare($queryString);
        $query->bindValue(':waterId', $waterId);

        $query->execute();
    }
}
