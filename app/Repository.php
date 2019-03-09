<?php

namespace App;

use function array_column;
use ParagonIE\EasyDB\EasyDB;
use ParagonIE\EasyDB\Factory;

class Repository
{
    /** @var EasyDB */
    private $db;

    /** @var array */
    private $idToName;

    const TABLE = 'passes';

    public function __construct($host, $dbname, $login, $password)
    {

        $this->db = Factory::create(
            "mysql:host=$host;dbname=$dbname",
            $login,
            $password
        );

        $this->idToName = array_column(include(__DIR__ . '/static/brightest_satellites.php'), 'name', 'id');
    }

    public function getClosestVisualPassByIP($utc_time, $ip)
    {
        $row = $this->db->row("select * from passes where ip = ? and start_utc > ? order by start_utc asc limit 1;", $ip, $utc_time);

        if ($row)
        {
            return new VisualPass($row['sat_id'], $this->idToName[$row['sat_id']], $row['start_utc'], $row['end_utc'], $row['duration'], $row['magnitude']);
        }

        return null;
    }

    public function getClosestVisualPassByCity($utc_time, $city)
    {
        $row = $this->db->row("select * from passes where city = ? and start_utc > ? order by start_utc asc limit 1;", $city, $utc_time);

        if ($row)
        {
            return new VisualPass($row['sat_id'], $this->idToName[$row['sat_id']], $row['start_utc'], $row['end_utc'], $row['duration'], $row['magnitude']);
        }

        return null;
    }

    public function getClosestVisualPassByRoundedLatLong($utc_time, $roundedLat, $roundedLong)
    {

        $row = $this->db->row("select * from passes where rounded_lat = ? and rounded_long = ? and start_utc > ? order by start_utc asc limit 1;",
                              $roundedLat, $roundedLong, $utc_time
        );

        if ($row)
        {
            return new VisualPass($row['sat_id'], $this->idToName[$row['sat_id']], $row['start_utc'], $row['end_utc'], $row['duration'], $row['magnitude']);
        }

        return null;
    }


    /**
     * @param VisualPass[]
     */
    public function insertVisualPasses(array $visualPasses, Location $location, $ip)
    {
        $maps = [];
        foreach ($visualPasses as $visualPass)
        {
            /** @var VisualPass $visualPass */
            $maps []= [
                'lat' => $location->getLat(),
                'lon' => $location->getLong(),
                'city' => $location->getName(),
                'rounded_lat' => $location->getRoundedLat(),
                'rounded_long' => $location->getRoundedLong(),
                'ip' => $ip,
                'sat_id' => $visualPass->id,
                'start_utc' => $visualPass->start_utc,
                'end_utc' => $visualPass->end_utc,
                'duration' => $visualPass->duration,
                'magnitude' => $visualPass->magnitude,
            ];


        }
        $this->db->insertMany(self::TABLE, $maps);
    }
}
