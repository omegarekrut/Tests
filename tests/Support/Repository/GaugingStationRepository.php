<?php

namespace Repository;

use Codeception\Module;
use PDO;

class GaugingStationRepository extends Module
{
    /**
     * @return string[]
     */
    public function grubActiveGaugingStationSlugAndShortUuidOnWaterWithSeveralGaugingStations(): array
    {
        $queryString = '
            SELECT *
            FROM (
                SELECT gauging_stations.water_id, gauging_stations.slug, gauging_stations.short_uuid
                FROM gauging_stations
                    LEFT JOIN gauging_station_providers ON gauging_stations.id = gauging_station_providers.gauging_station_id
                    LEFT JOIN gauging_station_provider_records ON (gauging_station_providers.id = gauging_station_provider_records.gauging_station_provider_id
                        AND gauging_station_provider_records.recorded_at >= DATE_SUB(NOW(), INTERVAL 10 DAY))
                WHERE gauging_stations.hidden = 0 
                    AND gauging_station_provider_records.recorded_at = (
                      SELECT MAX(gauging_station_provider_records.recorded_at)
                      FROM gauging_station_provider_records
                      WHERE gauging_station_providers.id = gauging_station_provider_records.gauging_station_provider_id
                    )
                GROUP BY gauging_stations.id
            ) as tmp
            GROUP BY tmp.water_id
            HAVING COUNT(*) > 1;
        ';

        $connection = $this->getModule('Db')->_getDbh();
        $query = $connection->prepare($queryString);
        $query->execute();

        $gaugingStationParams = $query->fetch(PDO::FETCH_ASSOC);

        return [
            'slug' => $gaugingStationParams['slug'],
            'shortUuid' => $gaugingStationParams['short_uuid'],
        ];
    }

    /**
     * @return string[]
     */
    public function grubInactiveGaugingStationSlugAndShortUuid(): array
    {
        $queryString = '
            SELECT *
                FROM (
                    SELECT gauging_stations.water_id, gauging_stations.slug, gauging_stations.short_uuid, MAX(gauging_station_provider_records.recorded_at) as max_date
                    FROM gauging_stations
                        LEFT JOIN gauging_station_providers ON gauging_stations.id = gauging_station_providers.gauging_station_id
                        LEFT JOIN gauging_station_provider_records ON gauging_station_providers.id = gauging_station_provider_records.gauging_station_provider_id
                        GROUP BY gauging_stations.id
                ) as tmp
                WHERE tmp.max_date < DATE_SUB(NOW(), INTERVAL 10 DAY)
        ';

        $connection = $this->getModule('Db')->_getDbh();
        $query = $connection->prepare($queryString);
        $query->execute();

        $gaugingStationParams = $query->fetch(PDO::FETCH_ASSOC);

        return [
            'slug' => $gaugingStationParams['slug'],
            'shortUuid' => $gaugingStationParams['short_uuid'],
        ];
    }

    /**
     * @return string[]
     */
    public function grubTheOnlyGaugingStationSlugAndShortUuid(): array
    {
        $queryString = '
            SELECT gauging_station.water_id, gauging_station.slug, gauging_station.short_uuid
            FROM gauging_stations gauging_station
            INNER JOIN waters ON gauging_station.water_id = waters.id
            WHERE waters.id NOT IN (
                SELECT parent_water_id AS id FROM waters WHERE parent_water_id IS NOT NULL
            )
            AND gauging_station.hidden = 0
            GROUP BY gauging_station.water_id
            HAVING COUNT(*) = 1;
        ';

        $connection = $this->getModule('Db')->_getDbh();
        $query = $connection->prepare($queryString);
        $query->execute();

        $gaugingStationParams = $query->fetch(PDO::FETCH_ASSOC);

        return [
            'slug' => $gaugingStationParams['slug'],
            'shortUuid' => $gaugingStationParams['short_uuid'],
        ];
    }

    /**
     * @return string[]
     */
    public function grubTheOnlyHiddenGaugingStationSlugAndShortUuid(): array
    {
        $queryString = '
            SELECT gauging_station.water_id, gauging_station.slug, gauging_station.short_uuid
            FROM gauging_stations gauging_station
            INNER JOIN waters ON gauging_station.water_id = waters.id
            WHERE waters.id NOT IN (
                SELECT parent_water_id AS id FROM waters WHERE parent_water_id IS NOT NULL
            )
            AND gauging_station.hidden = 1
            GROUP BY gauging_station.water_id
            HAVING COUNT(*) = 1;
        ';

        $connection = $this->getModule('Db')->_getDbh();
        $query = $connection->prepare($queryString);
        $query->execute();

        $gaugingStationParams = $query->fetch(PDO::FETCH_ASSOC);

        return [
            'slug' => $gaugingStationParams['slug'],
            'shortUuid' => $gaugingStationParams['short_uuid'],
        ];
    }

    public function grabGaugingStationByShortUuid(string $gaugingStationShortUuid): string
    {
        $queryString = '
            SELECT name
            FROM gauging_stations
            WHERE short_uuid = :gaugingStationShortUuid
        ';

        $connection = $this->getModule('Db')->_getDbh();
        assert($connection instanceof PDO);

        $query = $connection->prepare($queryString);
        $query->bindValue(':gaugingStationShortUuid', $gaugingStationShortUuid);
        $query->execute();

        $gaugingStation = $query->fetch(PDO::FETCH_ASSOC);

        return $gaugingStation['name'];
    }

    public function hideGaugingStationByShortUuid(string $gaugingStationShortUuid): void
    {
        $queryString = '
            UPDATE gauging_stations
            SET hidden = 1
            WHERE short_uuid = :gaugingStationShortUuid
        ';

        $connection = $this->getModule('Db')->_getDbh();
        assert($connection instanceof PDO);

        $query = $connection->prepare($queryString);
        $query->bindValue(':gaugingStationShortUuid', $gaugingStationShortUuid);
        $query->execute();
    }

    public function showGaugingStationByShortUuid(string $gaugingStationShortUuid): void
    {
        $queryString = '
            UPDATE gauging_stations
            SET hidden = 0
            WHERE short_uuid = :gaugingStationShortUuid
        ';

        $connection = $this->getModule('Db')->_getDbh();
        assert($connection instanceof PDO);

        $query = $connection->prepare($queryString);
        $query->bindValue(':gaugingStationShortUuid', $gaugingStationShortUuid);
        $query->execute();
    }
}
