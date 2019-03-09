<?php

namespace App;

use GeoIp2\Database\Reader;

class GeoLocator
{
    private $reader;

    public function __construct()
    {
        $this->reader = new Reader('/data/geoip/geolite_city.mmdb');
    }

    /**
     * @param $ip
     *
     * @return Location
     * @throws \GeoIp2\Exception\AddressNotFoundException
     * @throws \MaxMind\Db\Reader\InvalidDatabaseException
     */
    public function locate($ip)
    {
        $record = $this->reader->city($ip);


        return new Location($record->location->latitude, $record->location->longitude, $record->city->name);

    }
}
