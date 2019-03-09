<?php

namespace App;

class Location
{
    private $lat;
    private $long;
    private $alt;
    private $name;

    /**
     * @return mixed
     */
    public function getLat()
    {
        return $this->lat;
    }

    public function getRoundedLat()
    {
        return round($this->lat, 1); // in this case, the exact and rounded positions are 2 hours by car maximum
    }

    public function getRoundedLong()
    {
        return round($this->long, 1);
    }

    /**
     * @return mixed
     */
    public function getLong()
    {
        return $this->long;
    }

    /**
     * @return int
     */
    public function getAlt()
    {
        return $this->alt;
    }

    public function getName()
    {
        return $this->name;
    }

    public function __construct($lat, $long, $name = null, $alt = 0)
    {
        $this->lat = $lat;
        $this->long = $long;
        $this->name = $name;
        $this->alt = $alt;
    }

    public function __toString()
    {
        return "lat = {$this->lat}, long = {$this->long}, name = {$this->name}";
    }
}
